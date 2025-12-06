<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourtSchedule extends Model
{
    use HasFactory;

    protected $table = 'court_schedules';

    protected $fillable = [
        'event_type',
        'title',
        'description',
        'case_id',
        'case_number',
        'court_name',
        'court_room',
        'judge_name',
        'event_date',
        'event_time',
        'duration_minutes',
        'location',
        'status',
        'priority',
        'assigned_lawyer_id',
        'outcome',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'event_date' => 'date',
        'duration_minutes' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function case()
    {
        return $this->belongsTo(LegalCase::class, 'case_id');
    }

    public function assignedLawyer()
    {
        return $this->belongsTo(User::class, 'assigned_lawyer_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scopes
     */
    public function scopeUpcoming($query)
    {
        return $query->where('event_date', '>=', now())
            ->whereIn('status', ['Scheduled', 'Confirmed'])
            ->orderBy('event_date');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('event_date', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('event_date', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('event_type', $type);
    }

    public function scopeScheduled($query)
    {
        return $query->whereIn('status', ['Scheduled', 'Confirmed']);
    }

    /**
     * Helper methods
     */
    public function isUpcoming()
    {
        return $this->event_date >= now()->toDateString() &&
               in_array($this->status, ['Scheduled', 'Confirmed']);
    }

    public function isPast()
    {
        return $this->event_date < now()->toDateString();
    }

    public function isToday()
    {
        return $this->event_date->isToday();
    }

    public function isHighPriority()
    {
        return in_array($this->priority, ['High', 'Urgent']);
    }
}
