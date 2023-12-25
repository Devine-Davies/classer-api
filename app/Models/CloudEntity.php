<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CloudEntity extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     * - status
     * -- deleted
     * -- active
     * -- processing
     * @var array<int, string>
     */
    protected $fillable = [
        'uid',
        'user_id',
        'event_id',
        'entity_id',
        'entity_type',
        'status',
        'location',
        'size',
    ];
}