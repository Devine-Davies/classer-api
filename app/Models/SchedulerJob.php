<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchedulerJob extends Model
{
    use HasFactory;

    // mass assignment
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'command',
        'metadata',
        'scheduled_for',
    ];
}
