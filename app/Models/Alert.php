<?php

namespace App\Models;

class Alert extends BaseModel
{
    public $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    protected $table = 'alerts';

    protected $fillable = [
        'device_id',
        'telemetry_id',
        'type',
        'severity',
        'message',
        'triggered_at',
        'resolved_at',
        'acknowledged_at',
    ];
}