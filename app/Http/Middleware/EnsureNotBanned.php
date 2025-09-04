<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureNotBanned
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user) {
            // 兼容两种字段：banned_at（timestamp）或 is_banned（boolean）
            $attrs    = $user->getAttributes();
            $isBanned = array_key_exists('banned_at', $attrs)
                ? !is_null($user->banned_at)
                : (bool)($user->is_banned ?? false);

            if ($isBanned) {
                auth()->logout();
                return redirect()->route('login')->with('status', 'Your account is banned.');
            }
        }

        return $next($request);
    }
}
