<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReadingPlan extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'book_id',
        'due_date',
        'status',
        'completed_at',
    ];

    protected $casts = [
        'status' => \App\Enums\ReadingPlanStatus::class,
        'due_date' => 'date',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * Bladeで使うtarget_dateをdue_dateに対応させる
     */
    public function getTargetDateAttribute()
    {
        return $this->due_date;
    }

}