<?php

namespace App\Models;

use App\Enums\CloudEntityRole;
use App\Enums\CloudEntityStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CloudEntity extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $hidden = [
        'id',
        'key',
        'cloudable_id',
        'cloudable_type',
        'e_tag',
        'checksum',
    ];

    protected $fillable = [
        'uid',
        'key',

        'object_role',
        'original_name',
        'mime_type',

        'expected_size',
        'actual_size',

        'e_tag',
        'checksum',

        'status',

        'uploaded_at',
        'validated_at',
    ];

    protected $casts = [
        'status' => CloudEntityStatus::class,
        'object_role' => CloudEntityRole::class,

        'expected_size' => 'integer',
        'actual_size' => 'integer',

        'uploaded_at' => 'datetime',
        'validated_at' => 'datetime',
    ];

    public function cloudable()
    {
        return $this->morphTo();
    }
}
