<?php

namespace App\Models;

use App\Enums\CloudBackupStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CloudBackup extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'uid',
        'user_id',
        'resource_id',
        'expected_size',
        'actual_size',
        'status',
        'completed_at',
        'validated_at',
        'last_restored_at',
    ];

    protected $casts = [
        'expected_size' => 'integer',
        'actual_size' => 'integer',
        'status' => CloudBackupStatus::class,
        'completed_at' => 'datetime',
        'validated_at' => 'datetime',
        'last_restored_at' => 'datetime',
    ];

    public function cloudEntities(): MorphMany
    {
        return $this->morphMany(CloudEntity::class, 'cloudable');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'uid');
    }
}
