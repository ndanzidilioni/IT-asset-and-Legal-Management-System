<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'developer_id',
        'task_id',
        'start',
        'end',
        'type',
        'status',
        'notes'
    ];

    protected $casts = [
        'start' => 'datetime',
        'end' => 'datetime',
    ];

    /**
     * Get the developer for this schedule
     */
    public function developer()
    {
        return $this->belongsTo(User::class, 'developer_id');
    }

    /**
     * Get the task for this schedule
     */
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Scope for schedules by developer
     */
    public function scopeByDeveloper($query, $developerId)
    {
        return $query->where('developer_id', $developerId);
    }

    /**
     * Scope for schedules by date range
     */
    public function scopeByDateRange($query, $start, $end)
    {
        return $query->where('start', '>=', $start)
                    ->where('end', '<=', $end);
    }

    /**
     * Scope for available schedules
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'free');
    }

    /**
     * Scope for booked schedules
     */
    public function scopeBooked($query)
    {
        return $query->where('status', 'booked');
    }

    /**
     * Check if schedule conflicts with another
     */
    public function conflictsWith($start, $end)
    {
        return $this->where(function ($query) use ($start, $end) {
            $query->where(function ($q) use ($start, $end) {
                $q->where('start', '<', $end)
                  ->where('end', '>', $start);
            });
        })->exists();
    }

    /**
     * Get duration in hours
     */
    public function getDurationInHours()
    {
        return $this->start->diffInHours($this->end);
    }

    /**
     * Check if schedule is in the past
     */
    public function isPast()
    {
        return $this->end < now();
    }

    /**
     * Check if schedule is in the future
     */
    public function isFuture()
    {
        return $this->start > now();
    }

    /**
     * Check if schedule is currently active
     */
    public function isActive()
    {
        return $this->start <= now() && $this->end >= now();
    }
}














