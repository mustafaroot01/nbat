<?php

namespace App\Middleware;

use App\Models\Admin;
use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user() || !($request->user() instanceof Admin)) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بالوصول',
                'data' => null,
            ], 403);
        }

        if (!$request->user()->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'تم إيقاف حسابك، تواصل مع المشرف العام',
                'data' => null,
            ], 403);
        }

        return $next($request);
    }
}
