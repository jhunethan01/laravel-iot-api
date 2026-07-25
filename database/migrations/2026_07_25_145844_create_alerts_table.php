<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained('devices')->cascadeOnDelete();
            $table->foreignId('telemetry_id')->nullable()->constrained('telemetry')->nullOnDelete();
            $table->string('type', 50);
            $table->string('severity', 20);
            $table->text('message');
            $table->timestamp('triggered_at');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamps();

            $table->index(['device_id', 'triggered_at']);
            $table->index(['severity', 'triggered_at']);
            $table->index('resolved_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};

// id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
// device_id BIGINT UNSIGNED NOT NULL,
// telemetry_id BIGINT UNSIGNED NULL,
// type VARCHAR(50) NOT NULL,
// severity VARCHAR(20) NOT NULL,
// message TEXT NOT NULL,
// triggered_at TIMESTAMP NOT NULL,
// resolved_at TIMESTAMP NULL,
// acknowledged_at TIMESTAMP NULL,
// created_at TIMESTAMP NULL,
// updated_at TIMESTAMP NULL,
// FOREIGN KEY (device_id) REFERENCES devices(id) ON DELETE CASCADE,
// FOREIGN KEY (telemetry_id) REFERENCES telemetry(id) ON DELETE SET NULL

// INDEX idx_alerts_device_triggered_at (device_id, triggered_at),
// INDEX idx_alerts_severity_triggered_at (severity, triggered_at),
// INDEX idx_alerts_resolved_at (resolved_at)