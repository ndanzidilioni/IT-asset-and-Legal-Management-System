<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DemandNoteDeletionRequest extends Model
{
    use HasFactory;

    // Disable default timestamps since table uses custom column names
    public $timestamps = false;

    protected $fillable = [
        'demand_note_id',
        'requested_by',
        'requester_name',
        'reason',
        'status',
        'reviewed_by',
        'reviewer_name',
        'review_comment',
        'reviewed_at',
        'requested_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
        'requested_at' => 'datetime',
    ];

    /**
     * Get the demand note associated with this deletion request
     */
    public function demandNote()
    {
        return $this->belongsTo(DemandNote::class, 'demand_note_id');
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
