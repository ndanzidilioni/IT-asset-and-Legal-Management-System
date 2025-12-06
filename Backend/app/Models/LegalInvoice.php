<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LegalInvoice extends Model
{
    use HasFactory;

    protected $table = 'legal_invoices';

    protected $fillable = [
        'invoice_number',
        'client_id',
        'client_name',
        'case_id',
        'invoice_date',
        'due_date',
        'subtotal',
        'tax_amount',
        'total_amount',
        'amount_paid',
        'balance_due',
        'currency',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance_due' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = self::generateInvoiceNumber();
            }
            
            // Auto-calculate balance if not set
            if (is_null($invoice->balance_due)) {
                $invoice->balance_due = $invoice->total_amount - $invoice->amount_paid;
            }
        });

        static::updating(function ($invoice) {
            // Auto-update balance when payment changes
            $invoice->balance_due = $invoice->total_amount - $invoice->amount_paid;
            
            // Auto-update status based on payments
            if ($invoice->balance_due <= 0) {
                $invoice->status = 'Paid';
            } elseif ($invoice->amount_paid > 0 && $invoice->balance_due < $invoice->total_amount) {
                $invoice->status = 'Partial';
            } elseif ($invoice->due_date < now() && $invoice->balance_due > 0) {
                $invoice->status = 'Overdue';
            }
        });
    }

    /**
     * Generate unique invoice number (INV-YYYY-####)
     */
    public static function generateInvoiceNumber()
    {
        $year = date('Y');
        $prefix = "INV-{$year}-";
        
        $lastInvoice = self::where('invoice_number', 'like', "{$prefix}%")
            ->orderBy('invoice_number', 'desc')
            ->first();
        
        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice->invoice_number, -4);
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

    public function case()
    {
        return $this->belongsTo(LegalCase::class, 'case_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scopes
     */
    public function scopeUnpaid($query)
    {
        return $query->where('balance_due', '>', 0)
            ->whereIn('status', ['Sent', 'Partial', 'Overdue']);
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
            ->where('balance_due', '>', 0);
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'Paid');
    }

    /**
     * Helper methods
     */
    public function isPaid()
    {
        return $this->status === 'Paid' || $this->balance_due <= 0;
    }

    public function isOverdue()
    {
        return $this->due_date < now() && $this->balance_due > 0;
    }

    public function isPartiallyPaid()
    {
        return $this->amount_paid > 0 && $this->balance_due > 0;
    }

    public function getDaysOverdue()
    {
        if (!$this->isOverdue()) {
            return 0;
        }
        return now()->diffInDays($this->due_date);
    }

    public function getPaymentPercentage()
    {
        if ($this->total_amount == 0) {
            return 0;
        }
        return ($this->amount_paid / $this->total_amount) * 100;
    }
}
