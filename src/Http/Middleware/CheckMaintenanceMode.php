<?php

namespace Axvi\Maintenance\Http\Middleware;

use Axvi\Maintenance\Events\MaintenanceBypassGranted;
use Axvi\Maintenance\MaintenanceManager;
use Axvi\Maintenance\Models\MaintenanceSetting;
use Axvi\Maintenance\Models\MaintenanceToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    public function __construct(
        protected MaintenanceManager $manager,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        // Not in maintenance — pass through immediately
        if (! $this->manager->isDown()) {
            return $next($request);
        }

        // Skip excluded paths
        if ($this->isExcludedPath($request)) {
            return $next($request);
        }

        // Skip the bypass route itself
        if ($this->isBypassRoute($request)) {
            return $next($request);
        }

        // 1. IP whitelist check (cached)
        $ip = $request->ip();
        if ($ip && $this->manager->isIpAllowed($ip)) {
            event(new MaintenanceBypassGranted('ip', $ip));

            return $next($request);
        }

        // 2. Bypass cookie check
        $cookieName = config('maintenance.middleware.cookie_name', 'laravel_maintenance');
        $cookieValue = $request->cookie($cookieName);
        if ($cookieValue) {
            $token = MaintenanceToken::findByToken($cookieValue);
            if ($token) {
                $token->markUsed();
                event(new MaintenanceBypassGranted('cookie', $cookieValue));

                return $next($request);
            }
        }

        // 3. Header check (for APIs / headless apps)
        $headerName = config('maintenance.middleware.header_name', 'X-Maintenance-Token');
        $headerValue = $request->header($headerName);
        if ($headerValue) {
            $token = MaintenanceToken::findByToken($headerValue);
            if ($token) {
                $token->markUsed();
                event(new MaintenanceBypassGranted('header', $headerValue));

                return $next($request);
            }
        }

        // 4. Return 503 response
        return $this->buildMaintenanceResponse($request);
    }

    /**
     * Handle a request for a bypass secret URL: /maintenance/{token}
     * Called from ServiceProvider route registration.
     */
    public function handleBypassRoute(Request $request, string $token): Response
    {
        $record = MaintenanceToken::findByToken($token);

        if (! $record) {
            abort(404);
        }

        $record->markUsed();
        event(new MaintenanceBypassGranted('url', $token));

        $cookieName = config('maintenance.middleware.cookie_name', 'laravel_maintenance');
        $cookieLifetime = config('maintenance.middleware.cookie_lifetime', 43200);

        return redirect('/')->withCookie(
            cookie($cookieName, $token, $cookieLifetime, '/', null, true, true, false, 'strict')
        );
    }

    protected function isExcludedPath(Request $request): bool
    {
        $except = config('maintenance.except', []);

        foreach ($except as $pattern) {
            if ($request->is($pattern)) {
                return true;
            }
        }

        return false;
    }

    protected function isBypassRoute(Request $request): bool
    {
        if (! config('maintenance.bypass_route.enabled', true)) {
            return false;
        }

        $prefix = trim(config('maintenance.bypass_route.prefix', 'maintenance'), '/');

        return $request->is("{$prefix}/*");
    }

    protected function buildMaintenanceResponse(Request $request): Response
    {
        $setting = MaintenanceSetting::current();

        $retryAfter = $setting->retry_after ?? 60;
        $refresh = $setting->refresh;

        $headers = ['Retry-After' => $retryAfter];
        if ($refresh) {
            $headers['Refresh'] = $refresh;
        }

        // Return JSON for API / XMLHttpRequest
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $setting->message ?? 'Service Unavailable',
            ], 503, $headers);
        }

        return response()->view(
            config('maintenance.response.view', 'maintenance::503'),
            ['setting' => $setting],
            503,
            $headers
        );
    }
}
