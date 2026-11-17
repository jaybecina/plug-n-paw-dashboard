<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

/**
 * Keeps the public demo usable for the next visitor by refusing requests
 * that would change the shared admin account.
 */
class PreventDemoChanges
{
    /**
     * Route names that change credentials or the account itself.
     *
     * @var list<string>
     */
    public const PROTECTED_ROUTES = [
        'profile.update',
        'profile.destroy',
        'user-password.update',
        'password.update',
        'two-factor.enable',
        'two-factor.confirm',
        'two-factor.disable',
        'two-factor.regenerate-recovery-codes',
        'passkey.store',
        'passkey.destroy',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (! config('demo.enabled') || ! $request->routeIs(...self::PROTECTED_ROUTES)) {
            return $next($request);
        }

        Inertia::flash('toast', [
            'type' => 'error',
            'message' => __('Account changes are turned off in the live demo. Clone the repo to try them locally.'),
        ]);

        return back();
    }
}
