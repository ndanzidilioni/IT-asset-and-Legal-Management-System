<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractDocument extends Model
{
    protected $table = 'contract_documents';
    
    protected $fillable = [
        'contract_id',
        'document_name',
        'original_filename',
        'file_path',
        'file_size',
        'file_type',
        'document_type',
        'uploaded_by',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'contract_id' => 'integer',
        'uploaded_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function contract()
    {
        return $this->belongsTo(LegalContract::class, 'contract_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // Helper methods
    public function getFormattedFileSize()
    {
        $bytes = $this->file_size;
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    public function getFileExtension()
    {
        return pathinfo($this->original_filename, PATHINFO_EXTENSION);
    }
}
