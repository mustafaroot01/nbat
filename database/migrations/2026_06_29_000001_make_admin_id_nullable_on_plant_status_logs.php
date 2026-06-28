<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Auto-approved plants (trusted users / instant-approval mode) create a
     * status log with no admin, so admin_id must allow NULL.
     */
    public function up(): void
    {
        Schema::table('plant_status_logs', function (Blueprint $table) {
            $table->dropForeign(['admin_id']);
        });

        Schema::table('plant_status_logs', function (Blueprint $table) {
            $table->foreignId('admin_id')->nullable()->change();
            $table->foreign('admin_id')->references('id')->on('admins')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('plant_status_logs', function (Blueprint $table) {
            $table->dropForeign(['admin_id']);
        });

        Schema::table('plant_status_logs', function (Blueprint $table) {
            $table->foreignId('admin_id')->nullable(false)->change();
            $table->foreign('admin_id')->references('id')->on('admins')->cascadeOnDelete();
        });
    }
};
