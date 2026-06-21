<?php

use Illuminate\Support\Facades\Route;

// Dashboard SPA — serves built Vue.js app
Route::get('/dashboard/{any?}', function () {
    $path = public_path('dashboard/index.html');

    if (file_exists($path)) {
        return file_get_contents($path);
    }

    return 'Dashboard not built yet. Run: cd dashboard && npm run build';
})->where('any', '.*');

Route::get('/', function () {
    return redirect('/dashboard');
});
