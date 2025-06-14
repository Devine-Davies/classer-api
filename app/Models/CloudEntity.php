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

    // hidden 
    protected $hidden = [
        'id',
        'key',
        'cloudable_id',
        'cloudable_type',
        'e_tag',
        
    ];	

    protected $fillable = [
        'uid',
        'upload_url',
        'public_url',
        'e_tag',
        'key',
        'type',
        'size'
    ];

    public function cloudable()
    {
        return $this->morphTo();
    }
}