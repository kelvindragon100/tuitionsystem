<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && auth()->user()->role === 'admin') {
            return $next($request);
        }

        // 返回标准 403，符合安全实践（明确定义“拒绝访问”）
        abort(403, 'Forbidden: Admins only.');
    }
}
