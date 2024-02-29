<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TimeRegistration extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'task_id', 'date', 'description', 'time', 'user_id'
    ];

    protected $casts = [
        'date' => 'date:Y-m-d'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }
}
