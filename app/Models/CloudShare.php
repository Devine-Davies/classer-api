<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
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
        'size',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    /**
     * Get all of the cloud entities for the cloud share.
     */
    public function cloudEntities(): MorphMany
    {
        return $this->morphMany(CloudEntity::class, 'cloudable');
    }

    /**
     * Get the user that owns the cloud share.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'uid');
    }
}
