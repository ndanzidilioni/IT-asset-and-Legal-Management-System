<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LegalClient extends Model
{
    use HasFactory;

    protected $table = 'legal_clients';

    protected $fillable = [
        'client_number',
        'client_type',
        'full_name',
        'short_name',
        'email',
        'phone',
        'mobile',
        'address',
        'city',
        'region',
        'country',
        'company_registration_number',
        'tax_identification_number',
        'industry',
        'national_id',
        'passport_number',
        'date_of_birth',
        'gender',
        'billing_address',
        'payment_terms',
        'status',
        'notes',
        'assigned_lawyer_id',
        'created_by',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Boot method to auto-generate client number
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($client) {
            if (empty($client->client_number)) {
                $client->client_number = self::generateClientNumber();
            }
        });
    }

    /**
     * Generate unique client number (CLT-YYYY-####)
     */
    public static function generateClientNumber()
    {
        $year = date('Y');
        $prefix = "CLT-{$year}-";
        
        $lastClient = self::where('client_number', 'like', "{$prefix}%")
            ->orderBy('client_number', 'desc')
            ->first();
        
        if ($lastClient) {
            $lastNumber = (int) substr($lastClient->client_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Relationships
     */
    public function assignedLawyer()
    {
        return $this->belongsTo(User::class, 'assigned_lawyer_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function cases()
    {
        return $this->hasMany(LegalCase::class, 'client_id');
    }

    public function contracts()
    {
        return $this->hasMany(LegalContract::class, 'client_id');
    }

    public function invoices()
    {
        return $this->hasMany(LegalInvoice::class, 'client_id');
    }

    public function documents()
    {
        return $this->hasMany(LegalDocument::class, 'client_id');
    }

    public function timeEntries()
    {
        return $this->hasMany(LegalTimeEntry::class, 'client_id');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }

    public function scopeCorporate($query)
    {
        return $query->where('client_type', 'Corporate');
    }

    public function scopeIndividual($query)
    {
        return $query->where('client_type', 'Individual');
    }

    /**
     * Helper methods
     */
    public function isActive()
    {
        return $this->status === 'Active';
    }

    public function isCorporate()
    {
        return $this->client_type === 'Corporate';
    }

    public function getTotalCases()
    {
        return $this->cases()->count();
    }

    public function getActiveCases()
    {
        return $this->cases()->where('status', 'Active')->count();
    }

    public function getTotalInvoiced()
    {
        return $this->invoices()->sum('total_amount');
    }

    public function getTotalPaid()
    {
        return $this->invoices()->sum('amount_paid');
    }

    public function getOutstandingBalance()
    {
        return $this->invoices()->sum('balance_due');
    }
}
