<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Assignment extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'entity_type',
        'entity_id',
        'assigned_by',
        'status',
        'notes',
        'due_at',
        'submitted_at',
        'completed_at'
    ];

    protected $casts = [
        'due_at' => 'datetime',
        'submitted_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * The editor assigned to this work.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The admin who assigned the work.
     */
    public function assigner()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * The work itself (Book, Manuscript, Video...).
     */
    public function entity()
    {
        return $this->morphTo();
    }

    /**
     * Scope for pending assignments.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for active assignments.
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'in_progress']);
    }
}
