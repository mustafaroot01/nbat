<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$admin = \App\Models\Admin::first();
if ($admin) {
    try {
        $admin->assignRole('superadmin');
        echo "Role assigned.\n";
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
} else {
    echo "No admin found.\n";
}
echo "Roles in DB: " . json_encode(\DB::table('model_has_roles')->get()) . "\n";
