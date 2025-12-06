<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DemandNote extends Model
{
    protected $fillable = [
        'demand_note_number',
        'client_name',
        'client_number',
        'claim_reference',
        'amount_claimed',
        'due_date',
        'nature_of_claim',
        'agency_of_claim',
        'notice_to_institute_suit',
        'time_given_to_settle',
        'settlement_action_taken',
        'current_status',
        'remarks',
        'created_by',
        'issued_date',
        'payment_date',
        'amount_paid',
        'balance_due',
        'late_fee',
    ];

    protected $casts = [
        'amount_claimed' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance_due' => 'decimal:2',
        'late_fee' => 'decimal:2',
        'due_date' => 'date',
        'issued_date' => 'date',
        'payment_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Boot method to handle model events
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generate demand note number
        static::creating(function ($demandNote) {
            if (empty($demandNote->demand_note_number)) {
                $demandNote->demand_note_number = self::generateDemandNoteNumber();
            }
            
            // Set issued date if not set
            if (empty($demandNote->issued_date)) {
                $demandNote->issued_date = now();
            }
            
            // Calculate balance due
            $demandNote->balance_due = $demandNote->amount_claimed + $demandNote->late_fee - $demandNote->amount_paid;
        });

        // Recalculate balance on update
        static::updating(function ($demandNote) {
            $demandNote->balance_due = $demandNote->amount_claimed + $demandNote->late_fee - $demandNote->amount_paid;
            
            // Auto-update status based on payment
            if ($demandNote->amount_paid >= $demandNote->amount_claimed) {
                $demandNote->current_status = 'paid';
                if (empty($demandNote->payment_date)) {
                    $demandNote->payment_date = now();
                }
            } elseif ($demandNote->amount_paid > 0) {
                $demandNote->current_status = 'partially_paid';
            } elseif ($demandNote->due_date < now() && $demandNote->amount_paid == 0) {
                $demandNote->current_status = 'overdue';
            }
        });
    }

    /**
     * Generate unique demand note number
     */
    public static function generateDemandNoteNumber(): string
    {
        $prefix = 'DN';
        $year = date('Y');
        $lastNote = self::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();
        
        $sequence = $lastNote ? intval(substr($lastNote->demand_note_number, -4)) + 1 : 1;
        
        return sprintf('%s-%s-%04d', $prefix, $year, $sequence);
    }

    /**
     * Creator relationship
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Payments relationship
     */
    public function payments(): HasMany
    {
        return $this->hasMany(DemandNotePayment::class);
    }

    /**
     * Check if demand note is overdue
     */
    public function isOverdue(): bool
    {
        return $this->due_date < now() && $this->current_status !== 'paid' && $this->current_status !== 'cancelled';
    }

    /**
     * Get days overdue
     */
    public function getDaysOverdue(): int
    {
        if (!$this->isOverdue()) {
            return 0;
        }
        
        return now()->diffInDays($this->due_date);
    }

    /**
     * Calculate late fee based on days overdue
     */
    public function calculateLateFee(float $dailyRate = 0): float
    {
        if ($dailyRate <= 0) {
            return 0;
        }
        
        $daysOverdue = $this->getDaysOverdue();
        return $daysOverdue * $dailyRate;
    }

    /**
     * Get status badge color
     */
    public function getStatusColor(): string
    {
        return match($this->current_status) {
            'paid' => 'success',
            'pending' => 'warning',
            'partially_paid' => 'info',
            'overdue' => 'danger',
            'cancelled' => 'secondary',
            default => 'secondary',
        };
    }

    /**
     * Scope to get overdue notes
     */
    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
            ->whereNotIn('current_status', ['paid', 'cancelled']);
    }

    /**
     * Scope to get pending notes
     */
    public function scopePending($query)
    {
        return $query->where('current_status', 'pending');
    }

    /**
     * Scope to get paid notes
     */
    public function scopePaid($query)
    {
        return $query->where('current_status', 'paid');
    }
}
