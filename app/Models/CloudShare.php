<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CloudShare extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'cloud_share';
    protected $fillable = [
        'uid',
        'user_id',
        'resource_id',
        'expires_at',
        'size',
    ];

    protected $casts = [
        // 'expires_at' => 'datetime',
        'size' => 'integer',
        'total_usage' => 'integer', // ✅ Recommended
    ];

    public function cloudEntities()
    {
        return $this->morphMany(CloudEntity::class, 'cloudable');
    }
}