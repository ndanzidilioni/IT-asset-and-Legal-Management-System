<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'fname',
        'mname',
        'lname',
        'email',
        'username',
        'password',
        'role',
        'status',
        'privileges',
        'password_changed_at',
        'must_change_password',
        'failed_login_attempts',
        'locked_until',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'privileges' => 'array',
            'password_changed_at' => 'datetime',
            'must_change_password' => 'boolean',
            'locked_until' => 'datetime',
        ];
    }

    /**
     * Get the user's full name.
     */
    public function getFullNameAttribute(): string
    {
        $name = $this->fname;
        if ($this->mname) {
            $name .= ' ' . $this->mname;
        }
        $name .= ' ' . $this->lname;
        return trim($name);
    }

    /**
     * Check if user has a specific privilege.
     */
    public function hasPrivilege(string $privilege): bool
    {
        if ($this->role === 'admin') {
            return true; // Admins have all privileges
        }
        
        $privileges = $this->privileges ?? [];
        return in_array($privilege, $privileges);
    }

    /**
     * Grant a privilege to the user.
     */
    public function grantPrivilege(string $privilege): void
    {
        $privileges = $this->privileges ?? [];
        if (!in_array($privilege, $privileges)) {
            $privileges[] = $privilege;
            $this->privileges = $privileges;
            $this->save();
        }
    }

    /**
     * Revoke a privilege from the user.
     */
    public function revokePrivilege(string $privilege): void
    {
        $privileges = $this->privileges ?? [];
        $this->privileges = array_values(array_filter($privileges, fn($p) => $p !== $privilege));
        $this->save();
    }

    /**
     * Check if user account is locked.
     */
    public function isLocked(): bool
    {
        return $this->locked_until && $this->locked_until->isFuture();
    }

    /**
     * Get remaining lock time in minutes.
     */
    public function getRemainingLockMinutes(): int
    {
        if (!$this->isLocked()) {
            return 0;
        }
        
        return (int) ceil(now()->diffInMinutes($this->locked_until, false));
    }

    /**
     * Lock user account for specified minutes.
     */
    public function lockAccount(int $minutes = 4): void
    {
        $this->locked_until = now()->addMinutes($minutes);
        $this->save();
    }

    /**
     * Unlock user account.
     */
    public function unlockAccount(): void
    {
        $this->locked_until = null;
        $this->failed_login_attempts = 0;
        $this->save();
    }

    /**
     * Increment failed login attempts.
     */
    public function incrementFailedAttempts(): void
    {
        $this->failed_login_attempts++;
        
        // Lock account after 5 failed attempts
        if ($this->failed_login_attempts >= 5) {
            $this->lockAccount(4); // Lock for 4 minutes
        }
        
        $this->save();
    }

    /**
     * Reset failed login attempts.
     */
    public function resetFailedAttempts(): void
    {
        $this->failed_login_attempts = 0;
        $this->save();
    }

    /**
     * Check if password meets policy requirements.
     */
    public static function validatePasswordPolicy(string $password): array
    {
        $errors = [];
        
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters long';
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter';
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter';
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number';
        }
        
        if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
            $errors[] = 'Password must contain at least one special character';
        }
        
        return $errors;
    }

    /**
     * Get available privileges list.
     */
    public static function getAvailablePrivileges(): array
    {
        return [
            'view_assets' => 'View IT Assets',
            'edit_assets' => 'Edit IT Assets',
            'delete_assets' => 'Delete IT Assets',
            'manage_users' => 'Manage Users',
            'manage_tasks' => 'Manage Tasks',
            'view_reports' => 'View Reports',
            'manage_schedules' => 'Manage Schedules',
            'client_access' => 'Client Access',
            'admin_panel' => 'Admin Panel Access',
            'export_data' => 'Export Data',
            'import_data' => 'Import Data',
        ];
    }
}
