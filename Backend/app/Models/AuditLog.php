<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    public $timestamps = false; // We use created_at only
    
    protected $fillable = [
        'user_id',
        'username',
        'action',
        'description',
        'ip_address',
        'user_agent',
        'request_method',
        'request_url',
        'request_data',
        'response_status',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Get the user associated with the audit log.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Log a user activity.
     */
    public static function log(string $action, ?string $description = null, ?array $metadata = null): void
    {
        $user = auth()->user();
        $request = request();

        self::create([
            'user_id' => $user?->id,
            'username' => $user?->username ?? $user?->email ?? 'guest',
            'action' => $action,
            'description' => $description,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'request_method' => $request->method(),
            'request_url' => $request->fullUrl(),
            'request_data' => self::sanitizeRequestData($request->all()),
            'metadata' => $metadata,
        ]);
    }

    /**
     * Sanitize request data to remove sensitive information.
     */
    private static function sanitizeRequestData(array $data): string
    {
        $sanitized = $data;
        
        // Remove sensitive fields
        $sensitiveFields = ['password', 'password_confirmation', 'current_password', 'new_password', 'token', 'access_token'];
        
        foreach ($sensitiveFields as $field) {
            if (isset($sanitized[$field])) {
                $sanitized[$field] = '***REDACTED***';
            }
        }
        
        return json_encode($sanitized);
    }

    /**
     * Get activity type label.
     */
    public function getActivityTypeAttribute(): string
    {
        $actionMap = [
            'login' => 'Login',
            'logout' => 'Logout',
            'create' => 'Created',
            'update' => 'Updated',
            'delete' => 'Deleted',
            'view' => 'Viewed',
            'export' => 'Exported',
            'import' => 'Imported',
        ];

        foreach ($actionMap as $key => $label) {
            if (stripos($this->action, $key) !== false) {
                return $label;
            }
        }

        return 'Activity';
    }

    /**
     * Get formatted timestamp.
     */
    public function getFormattedTimeAttribute(): string
    {
        return $this->created_at->format('Y-m-d H:i:s');
    }
}
