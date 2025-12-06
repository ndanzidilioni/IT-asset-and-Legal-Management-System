<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourtProceeding extends Model
{
    use HasFactory;

    protected $table = 'court_proceedings';

    protected $fillable = [
        'case_id',
        'hearing_date',
        'name_of_court',
        'parties',
        'case_number',
        'court_judge',
        'clerk_karani',
        'advocate_for_opponent',
        'advocate_for_moi',
        'proceedings',
        'court_order',
        'next_date',
        'remarks',
        'created_by',
    ];

    protected $casts = [
        'hearing_date' => 'date',
        'next_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function case()
    {
        return $this->belongsTo(LegalCase::class, 'case_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
