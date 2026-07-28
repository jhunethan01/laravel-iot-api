<?php

namespace App\Models;

class Telemetry extends BaseModel
{
    /**
     * Role constants
     */

    /**
     * @var string Auto increments integer key column
     */
    public $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    protected $table = 'telemetry';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'device_id',
        'recorded_at',
        'temperature',
        'battery',
        'storage_used',
        'latitude',
        'longitude',
        'online'
    ];
}
