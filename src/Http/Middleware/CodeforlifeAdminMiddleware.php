<?php

namespace NCH\Codeforlife\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use NCH\Codeforlife\Models\User;

class CodeforlifeAdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!Auth::guest()) {
            $user = User::find(Auth::id());

            return $user->hasPermission(
                config('codeforlife.user.admin_role', 'browse_admin')
            ) ? $next($request) : redirect('/');
        }

        return redirect(route('codeforlife.login'));
    }
}
