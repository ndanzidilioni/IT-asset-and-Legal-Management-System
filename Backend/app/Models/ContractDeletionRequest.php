<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractDeletionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_id',
        'requested_by',
        'requester_name',
        'reason',
        'status',
        'reviewed_by',
        'reviewer_name',
        'review_comment',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    /**
     * Get the contract associated with this deletion request
     */
    public function contract()
    {
        return $this->belongsTo(LegalContract::class, 'contract_id');
    }

    /**
     * Get the user who requested the deletion
     */
    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * Get the user who reviewed the request
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
