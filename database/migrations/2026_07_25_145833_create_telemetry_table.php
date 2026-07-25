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
        Schema::create('telemetry', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained('devices')->cascadeOnDelete();
            $table->timestamp('recorded_at');
            $table->decimal('temperature', 5, 2);
            $table->unsignedTinyInteger('battery');
            $table->unsignedTinyInteger('storage_used');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->boolean('online')->default(true);
            $table->timestamps();

            $table->index(['device_id', 'recorded_at']);
            $table->unique(['device_id', 'recorded_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telemetry');
    }
};

// id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
// device_id BIGINT UNSIGNED NOT NULL,
// recorded_at TIMESTAMP NOT NULL,
// temperature DECIMAL(5,2) NOT NULL,
// battery TINYINT UNSIGNED NOT NULL,
// storage_used TINYINT UNSIGNED NOT NULL,
// latitude DECIMAL(10,7) NOT NULL,
// longitude DECIMAL(10,7) NOT NULL,
// online BOOLEAN NOT NULL DEFAULT TRUE,
// created_at TIMESTAMP NULL,
// updated_at TIMESTAMP NULL,
// FOREIGN KEY (device_id) REFERENCES devices(id) ON DELETE CASCADE

// INDEX idx_telemetry_device_recorded_at (device_id, recorded_at),
// UNIQUE KEY uq_telemetry_device_recorded_at (device_id, recorded_at)