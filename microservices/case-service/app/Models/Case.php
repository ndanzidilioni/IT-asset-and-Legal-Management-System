<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Case extends Model
{
    use HasFactory;

    protected $fillable = [
        'case_number',
        'title',
        'description',
        'case_type',
        'status',
        'priority',
        'client_id',
        'assigned_lawyer_id',
        'assigned_legal_assistant_id',
        'court_name',
        'court_location',
        'filing_date',
        'hearing_date',
        'deadline_date',
        'estimated_completion_date',
        'actual_completion_date',
        'case_value',
        'outcome',
        'notes',
        'is_confidential',
        'is_active'
    ];

    protected $casts = [
        'filing_date' => 'date',
        'hearing_date' => 'datetime',
        'deadline_date' => 'datetime',
        'estimated_completion_date' => 'date',
        'actual_completion_date' => 'date',
        'case_value' => 'decimal:2',
        'is_confidential' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the client for this case
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the assigned lawyer for this case
     */
    public function assignedLawyer()
    {
        return $this->belongsTo(User::class, 'assigned_lawyer_id');
    }

    /**
     * Get the assigned legal assistant for this case
     */
    public function assignedLegalAssistant()
    {
        return $this->belongsTo(User::class, 'assigned_legal_assistant_id');
    }

    /**
     * Get all case documents
     */
    public function documents()
    {
        return $this->hasMany(CaseDocument::class);
    }

    /**
     * Get all case activities/timeline
     */
    public function activities()
    {
        return $this->hasMany(CaseActivity::class);
    }

    /**
     * Get all case deadlines
     */
    public function deadlines()
    {
        return $this->hasMany(CaseDeadline::class);
    }

    /**
     * Get all case hearings
     */
    public function hearings()
    {
        return $this->hasMany(CaseHearing::class);
    }

    /**
     * Get all case expenses
     */
    public function expenses()
    {
        return $this->hasMany(CaseExpense::class);
    }

    /**
     * Get all case time entries
     */
    public function timeEntries()
    {
        return $this->hasMany(CaseTimeEntry::class);
    }

    /**
     * Scope for active cases
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for cases by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for cases by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('case_type', $type);
    }

    /**
     * Scope for cases by assigned lawyer
     */
    public function scopeByLawyer($query, $lawyerId)
    {
        return $query->where('assigned_lawyer_id', $lawyerId);
    }

    /**
     * Scope for cases by client
     */
    public function scopeByClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    /**
     * Scope for cases with upcoming deadlines
     */
    public function scopeUpcomingDeadlines($query, $days = 7)
    {
        return $query->where('deadline_date', '<=', now()->addDays($days))
                    ->where('deadline_date', '>=', now());
    }

    /**
     * Scope for overdue cases
     */
    public function scopeOverdue($query)
    {
        return $query->where('deadline_date', '<', now())
                    ->where('status', '!=', 'completed');
    }

    /**
     * Check if case is overdue
     */
    public function isOverdue()
    {
        return $this->deadline_date && 
               $this->deadline_date < now() && 
               $this->status !== 'completed';
    }

    /**
     * Check if case has upcoming deadline
     */
    public function hasUpcomingDeadline($days = 7)
    {
        return $this->deadline_date && 
               $this->deadline_date <= now()->addDays($days) &&
               $this->deadline_date >= now();
    }

    /**
     * Get case duration in days
     */
    public function getDurationInDays()
    {
        if ($this->actual_completion_date) {
            return $this->filing_date->diffInDays($this->actual_completion_date);
        }
        
        return $this->filing_date->diffInDays(now());
    }

    /**
     * Get total billable hours for this case
     */
    public function getTotalBillableHours()
    {
        return $this->timeEntries()->sum('hours');
    }

    /**
     * Get total expenses for this case
     */
    public function getTotalExpenses()
    {
        return $this->expenses()->sum('amount');
    }

    /**
     * Generate unique case number
     */
    public static function generateCaseNumber()
    {
        $year = now()->year;
        $prefix = 'CASE';
        
        $lastCase = self::whereYear('created_at', $year)
                      ->orderBy('id', 'desc')
                      ->first();
        
        if ($lastCase && $lastCase->case_number) {
            $lastNumber = (int) substr($lastCase->case_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . $year . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get case status color for UI
     */
    public function getStatusColor()
    {
        return match($this->status) {
            'open' => 'green',
            'in_progress' => 'blue',
            'pending' => 'yellow',
            'completed' => 'gray',
            'closed' => 'red',
            'cancelled' => 'red',
            default => 'gray'
        };
    }

    /**
     * Get case priority color for UI
     */
    public function getPriorityColor()
    {
        return match($this->priority) {
            'low' => 'green',
            'medium' => 'yellow',
            'high' => 'orange',
            'urgent' => 'red',
            default => 'gray'
        };
    }
}
