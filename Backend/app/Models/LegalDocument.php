<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LegalDocument extends Model
{
    use HasFactory;

    protected $table = 'legal_documents';

    public $timestamps = false; // Only has created_at

    protected $fillable = [
        'document_name',
        'document_type',
        'file_name',
        'file_path',
        'file_size',
        'case_id',
        'client_id',
        'contract_id',
        'status',
        'uploaded_by',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'created_at' => 'datetime',
    ];

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

    public function contract()
    {
        return $this->belongsTo(LegalContract::class, 'contract_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Scopes
     */
    public function scopeByType($query, $type)
    {
        return $query->where('document_type', $type);
    }

    public function scopeByCase($query, $caseId)
    {
        return $query->where('case_id', $caseId);
    }

    public function scopeByClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeByContract($query, $contractId)
    {
        return $query->where('contract_id', $contractId);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'Approved');
    }

    /**
     * Helper methods
     */
    public function getFileSizeFormatted()
    {
        if (!$this->file_size) {
            return 'Unknown';
        }

        $size = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }

        return round($size, 2) . ' ' . $units[$i];
    }

    public function getFileExtension()
    {
        return pathinfo($this->file_name, PATHINFO_EXTENSION);
    }

    public function isImage()
    {
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg'];
        return in_array(strtolower($this->getFileExtension()), $imageExtensions);
    }

    public function isPdf()
    {
        return strtolower($this->getFileExtension()) === 'pdf';
    }

    public function isDocument()
    {
        $docExtensions = ['doc', 'docx', 'txt', 'rtf', 'odt'];
        return in_array(strtolower($this->getFileExtension()), $docExtensions);
    }
}
