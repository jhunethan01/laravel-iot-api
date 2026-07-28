<?php

namespace App\Models;

class Device extends BaseModel
{
    /**
     * @var string Auto increments integer key column
     */
    public $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $table = 'devices';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'device_key',
        'name',
        'model',
        'status',
        'last_seen_at',
        'latest_temperature',
        'latest_battery',
        'latest_storage_used',
        'latest_latitude',
        'latest_longitude',
    ];
}
