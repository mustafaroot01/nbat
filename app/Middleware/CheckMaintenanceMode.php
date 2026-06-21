<?php

namespace App\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;

class CheckMaintenanceMode
{
    public function handle(Request $request, Closure $next)
    {
        if (Setting::getValue('maintenance_mode') === 'true') {
            return response()->json([
                'success' => false,
                'message' => 'التطبيق تحت الصيانة حالياً',
                'data' => null,
            ], 503);
        }

        return $next($request);
    }
}
