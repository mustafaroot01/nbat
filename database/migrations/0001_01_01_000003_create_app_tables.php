<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('image');
            $table->string('link')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('plants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('governorate_id')->constrained();
            $table->string('name');
            $table->string('type');
            $table->string('age');
            $table->string('image')->nullable();
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->geometry('location');
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->index();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('admins');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('device_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('token');
            $table->enum('platform', ['android', 'ios']);
            $table->timestamps();
        });

        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body');
            $table->enum('target', ['all', 'android', 'ios', 'topic']);
            $table->string('topic')->nullable();
            $table->foreignId('sent_by')->constrained('admins');
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('app_versions', function (Blueprint $table) {
            $table->id();
            $table->enum('platform', ['android', 'ios']);
            $table->string('version_number');
            $table->integer('version_code');
            $table->enum('update_type', ['optional', 'forced']);
            $table->string('store_url');
            $table->text('release_notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('app_versions');
        Schema::dropIfExists('notification_logs');
        Schema::dropIfExists('device_tokens');
        Schema::dropIfExists('plants');
        Schema::dropIfExists('banners');
    }
};
