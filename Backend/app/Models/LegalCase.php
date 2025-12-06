<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LegalCase extends Model
{
    use HasFactory;

    protected $table = 'legal_cases';

    protected $fillable = [
        'case_number',
        'case_year',
        'case_type',
        'parties',
        'title',
        'description',
        'client_id',
        'client_name',
        'court_name',
        'judge_name',
        'filing_date',
        'hearing_date',
        'next_hearing_date',
        'expected_completion_date',
        'closed_date',
        'status',
        'priority',
        'estimated_value',
        'amount_in_claim',
        'legal_fees',
        'documents_path',
        'notes',
        'outcome',
        'any_appeal',
        'assigned_lawyer',
        'assigned_lawyer_id',
        'created_by',
    ];

    protected $casts = [
        'filing_date' => 'date',
        'hearing_date' => 'date',
        'expected_completion_date' => 'date',
        'closed_date' => 'date',
        'estimated_value' => 'decimal:2',
        'amount_in_claim' => 'decimal:2',
        'legal_fees' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Boot method to auto-generate case number
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($case) {
            if (empty($case->case_number)) {
                $case->case_number = self::generateCaseNumber();
            }
        });
    }

    /**
     * Generate unique case number (CASE-YYYY-####)
     */
    public static function generateCaseNumber()
    {
        $year = date('Y');
        $prefix = "CASE-{$year}-";
        
        $lastCase = self::where('case_number', 'like', "{$prefix}%")
            ->orderBy('case_number', 'desc')
            ->first();
        
        if ($lastCase) {
            $lastNumber = (int) substr($lastCase->case_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Relationships
     */
    public function client()
    {
        return $this->belongsTo(LegalClient::class, 'client_id');
    }

    public function assignedLawyer()
    {
        return $this->belongsTo(User::class, 'assigned_lawyer_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function courtSchedules()
    {
        return $this->hasMany(CourtSchedule::class, 'case_id');
    }

    public function documents()
    {
        return $this->hasMany(LegalDocument::class, 'case_id');
    }

    public function invoices()
    {
        return $this->hasMany(LegalInvoice::class, 'case_id');
    }

    public function timeEntries()
    {
        return $this->hasMany(LegalTimeEntry::class, 'case_id');
    }

    public function courtProceedings()
    {
        return $this->hasMany(CourtProceeding::class, 'case_id');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'Pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'Completed');
    }

    public function scopeHighPriority($query)
    {
        return $query->whereIn('priority', ['High', 'Urgent']);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('case_type', $type);
    }

    /**
     * Helper methods
     */
    public function isActive()
    {
        return $this->status === 'Active';
    }

    public function isClosed()
    {
        return in_array($this->status, ['Completed', 'Closed', 'Dismissed']);
    }

    public function isHighPriority()
    {
        return in_array($this->priority, ['High', 'Urgent']);
    }

    public function getDaysOpen()
    {
        if ($this->filing_date) {
            $endDate = $this->closed_date ?: now();
            return $this->filing_date->diffInDays($endDate);
        }
        return 0;
    }

    public function getNextHearing()
    {
        return $this->courtSchedules()
            ->where('event_type', 'Hearing')
            ->where('event_date', '>=', now())
            ->where('status', 'Scheduled')
            ->orderBy('event_date')
            ->first();
    }

    public function getTotalBilledHours()
    {
        return $this->timeEntries()->sum('hours');
    }

    public function getTotalBilledAmount()
    {
        return $this->timeEntries()->sum('amount');
    }
}
