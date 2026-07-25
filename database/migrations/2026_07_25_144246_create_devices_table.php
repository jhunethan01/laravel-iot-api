<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->string('device_key', 64)->unique();
            $table->string('name', 100)->nullable();
            $table->string('model', 100)->nullable();
            $table->string('status', 20)->default('offline');
            $table->timestamp('last_seen_at')->nullable();
            $table->decimal('latest_temperature', 5, 2)->nullable();
            $table->unsignedTinyInteger('latest_battery')->nullable();
            $table->unsignedTinyInteger('latest_storage_used')->nullable();
            $table->decimal('latest_latitude', 10, 7)->nullable();
            $table->decimal('latest_longitude', 10, 7)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};

// id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
// device_key VARCHAR(64) NOT NULL UNIQUE,
// name VARCHAR(100) NULL,
// model VARCHAR(100) NULL,
// status VARCHAR(20) NOT NULL DEFAULT 'offline',
// last_seen_at TIMESTAMP NULL,
// latest_temperature DECIMAL(5,2) NULL,
// latest_battery TINYINT UNSIGNED NULL,
// latest_storage_used TINYINT UNSIGNED NULL,
// latest_latitude DECIMAL(10,7) NULL,
// latest_longitude DECIMAL(10,7) NULL,
// created_at TIMESTAMP NULL,
// updated_at TIMESTAMP NULL