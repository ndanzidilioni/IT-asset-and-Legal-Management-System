<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaseActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'case_id',
        'user_id',
        'activity_type',
        'title',
        'description',
        'activity_date',
        'is_public',
        'metadata'
    ];

    protected $casts = [
        'activity_date' => 'datetime',
        'is_public' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Get the case for this activity
     */
    public function case()
    {
        return $this->belongsTo(Case::class);
    }

    /**
     * Get the user who performed this activity
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for public activities
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope for activities by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('activity_type', $type);
    }

    /**
     * Scope for activities by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for recent activities
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('activity_date', '>=', now()->subDays($days));
    }
}
