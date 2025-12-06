<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeveloperAvailability extends Model
{
    use HasFactory;
    protected $fillable = ['developer_id','availability'];
    protected $casts = ['availability' => 'array'];
}
