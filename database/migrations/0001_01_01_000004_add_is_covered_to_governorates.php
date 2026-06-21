<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('governorates', function (Blueprint $table) {
            if (!Schema::hasColumn('governorates', 'is_covered')) {
                $table->boolean('is_covered')->default(true)->after('name_en');
            }
        });
    }

    public function down(): void
    {
        Schema::table('governorates', function (Blueprint $table) {
            $table->dropColumn('is_covered');
        });
    }
};
