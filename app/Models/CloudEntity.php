<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

// create an example php file that defines a CloudEntity model with a polymorphic relationship 
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
    use SoftDeletes;

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
        'expires_at',
        'e_tag',
        'key',
        'type',
        'size'
    ];

    // Model
    protected $casts = ['expires_at' => 'datetime'];

    public function cloudable()
    {
        return $this->morphTo();
    }
}
