<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// create an example php enumb 
abstract class CloudEntityType
{
    const MOMENT = 'moment';
    const EVENT = 'event';
}

abstract class CloudEntityStatus
{
    const ACTIVE = 1;
    const PROCESSING = 2;
    const SCHEDULED_FOR_DELETION = 3;
    const DELETED = 4;
}


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