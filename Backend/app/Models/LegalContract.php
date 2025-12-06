<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LegalContract extends Model
{
    use HasFactory;

    protected $table = 'contracts';

    protected $fillable = [
        'contract_number',
        'title',
        'client_name',
        'client_id', 
        'contract_type',
        'status',
        'start_date',
        'end_date',
        'contract_value',
        'currency',
        'description',
        'terms_and_conditions',
        'payment_terms',
        'renewal_terms',
        'termination_clause',
        'governing_law',
        'jurisdiction',
        'signatory_client',
        'signatory_company',
        'created_by',
    ];

    protected $casts = [
        'contract_value' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'witness_signature_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($contract) {
            if (empty($contract->contract_number)) {
                $contract->contract_number = self::generateContractNumber();
            }
        });
    }

    /**
     * Generate unique contract number (CNT-YYYY-####)
     */
    public static function generateContractNumber()
    {
        $year = date('Y');
        $prefix = "CNT-{$year}-";
        
        $lastContract = self::where('contract_number', 'like', "{$prefix}%")
            ->orderBy('contract_number', 'desc')
            ->first();
        
        if ($lastContract) {
            $lastNumber = (int) substr($lastContract->contract_number, -4);
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

    public function manager()
    {
        return $this->belongsTo(User::class, 'managed_by');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function documents()
    {
        return $this->hasMany(\App\Models\ContractDocument::class, 'contract_id');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }

    public function scopeExpiring($query, $days = 30)
    {
        $futureDate = now()->addDays($days);
        return $query->where('status', 'Active')
            ->where('end_date', '<=', $futureDate)
            ->where('end_date', '>=', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('end_date', '<', now());
    }

    public function scopeByType($query, $type)
    {
        return $query->where('contract_type', $type);
    }

    /**
     * Helper methods
     */
    public function isActive()
    {
        return $this->status === 'Active';
    }

    public function isExpired()
    {
        return $this->end_date && $this->end_date->isPast();
    }

    public function isExpiringSoon($days = 30)
    {
        if (!$this->end_date) {
            return false;
        }
        $daysUntilExpiry = now()->diffInDays($this->end_date, false);
        return $daysUntilExpiry >= 0 && $daysUntilExpiry <= $days;
    }

    public function getDaysUntilExpiry()
    {
        if (!$this->end_date) {
            return null;
        }
        return now()->diffInDays($this->end_date, false);
    }

    public function getContractDuration()
    {
        if ($this->start_date && $this->end_date) {
            return $this->start_date->diffInDays($this->end_date);
        }
        return null;
    }
}
