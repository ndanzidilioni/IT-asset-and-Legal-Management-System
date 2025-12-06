<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LegalTimeEntry extends Model
{
    use HasFactory;

    protected $table = 'legal_time_entries';

    public $timestamps = false; // Only has created_at

    protected $fillable = [
        'case_id',
        'client_id',
        'entry_date',
        'hours',
        'description',
        'hourly_rate',
        'amount',
        'billable',
        'billed',
        'lawyer_id',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'hours' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'amount' => 'decimal:2',
        'billable' => 'boolean',
        'billed' => 'boolean',
        'created_at' => 'datetime',
    ];

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($timeEntry) {
            // Auto-calculate amount if not set
            if (is_null($timeEntry->amount)) {
                $timeEntry->amount = $timeEntry->hours * $timeEntry->hourly_rate;
            }
        });

        static::updating(function ($timeEntry) {
            // Recalculate amount if hours or rate changes
            $timeEntry->amount = $timeEntry->hours * $timeEntry->hourly_rate;
        });
    }

    /**
     * Relationships
     */
    public function case()
    {
        return $this->belongsTo(LegalCase::class, 'case_id');
    }

    public function client()
    {
        return $this->belongsTo(LegalClient::class, 'client_id');
    }

    public function lawyer()
    {
        return $this->belongsTo(User::class, 'lawyer_id');
    }

    /**
     * Scopes
     */
    public function scopeBillable($query)
    {
        return $query->where('billable', true);
    }

    public function scopeUnbilled($query)
    {
        return $query->where('billable', true)
            ->where('billed', false);
    }

    public function scopeBilled($query)
    {
        return $query->where('billed', true);
    }

    public function scopeByLawyer($query, $lawyerId)
    {
        return $query->where('lawyer_id', $lawyerId);
    }

    public function scopeByCase($query, $caseId)
    {
        return $query->where('case_id', $caseId);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('entry_date', [$startDate, $endDate]);
    }

    /**
     * Helper methods
     */
    public function isBillable()
    {
        return $this->billable === true;
    }

    public function isBilled()
    {
        return $this->billed === true;
    }

    public function isUnbilled()
    {
        return $this->billable && !$this->billed;
    }

    public function markAsBilled()
    {
        $this->billed = true;
        $this->save();
    }
}
