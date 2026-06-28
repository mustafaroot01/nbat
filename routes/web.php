<?php

use Illuminate\Support\Facades\Route;

// Dashboard SPA — serves built Vue.js app
$dashboardResponse = function () {
    $path = public_path('dashboard/index.html');

    if (file_exists($path)) {
        return response(file_get_contents($path), 200)
            ->header('Content-Type', 'text/html')
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    return 'Dashboard not built yet. Run: cd dashboard && npm run build';
};

Route::get('/dashboard', $dashboardResponse);
Route::get('/dashboard/{any}', $dashboardResponse)->where('any', '.+');

Route::get('/', function () {
    return redirect('/dashboard');
});
