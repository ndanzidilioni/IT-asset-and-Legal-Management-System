<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DemandNotePayment extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
        'demand_note_id',
        'payment_amount',
        'payment_date',
        'payment_method',
        'receipt_number',
        'notes',
        'recorded_by',
    ];

    protected $casts = [
        'payment_amount' => 'decimal:2',
        'payment_date' => 'date',
        'created_at' => 'datetime',
    ];

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        // Update demand note when payment is added
        static::created(function ($payment) {
            $demandNote = $payment->demandNote;
            $totalPaid = $demandNote->payments()->sum('payment_amount');
            $demandNote->update(['amount_paid' => $totalPaid]);
        });

        // Update demand note when payment is deleted
        static::deleted(function ($payment) {
            $demandNote = $payment->demandNote;
            $totalPaid = $demandNote->payments()->sum('payment_amount');
            $demandNote->update(['amount_paid' => $totalPaid]);
        });
    }

    /**
     * Demand note relationship
     */
    public function demandNote(): BelongsTo
    {
        return $this->belongsTo(DemandNote::class);
    }

    /**
     * Recorded by relationship
     */
    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
