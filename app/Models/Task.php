<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'status',
        'priority',
        'due_date',
        'completed_at',
    ];

    protected $casts = [
        'due_date' => 'date:Y-m-d',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Keep completed_at in sync with status so the two can never disagree.
     */
    protected static function booted(): void
    {
        static::saving(function (Task $task) {
            if ($task->status === 'done' && $task->completed_at === null) {
                $task->completed_at = now();
            }

            if ($task->status !== 'done') {
                $task->completed_at = null;
            }
        });
    }
}
