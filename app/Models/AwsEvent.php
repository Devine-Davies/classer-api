<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AwsEvent extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'bucket',
        'region',
        'user_identity',
        'owner_identity',
        'entity_id', // 'cloud_entity_id
        'arn',
        'time',
        'payload',
    ];
}
