<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchedulerModel extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'scheduler';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'command',
        'metadata',
        'scheduled_for',
    ];
}
