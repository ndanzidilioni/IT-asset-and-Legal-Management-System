<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'department',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
    ];

    /**
     * Get schedules for this user
     */
    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'developer_id');
    }

    /**
     * Get available schedules
     */
    public function availableSchedules()
    {
        return $this->schedules()->where('status', 'free');
    }

    /**
     * Get booked schedules
     */
    public function bookedSchedules()
    {
        return $this->schedules()->where('status', 'booked');
    }

    /**
     * Check if user is available at specific time
     */
    public function isAvailableAt($start, $end)
    {
        return !$this->schedules()
            ->where(function ($query) use ($start, $end) {
                $query->where('start', '<', $end)
                      ->where('end', '>', $start);
            })
            ->exists();
    }

    /**
     * Get free time slots for a date range
     */
    public function getFreeSlots($start, $end)
    {
        $schedules = $this->schedules()
            ->where('start', '>=', $start)
            ->where('end', '<=', $end)
            ->orderBy('start')
            ->get();

        $freeSlots = [];
        $current = Carbon::parse($start);

        foreach ($schedules as $schedule) {
            if ($current < $schedule->start) {
                $freeSlots[] = [
                    'start' => $current->toDateTimeString(),
                    'end' => $schedule->start->toDateTimeString(),
                ];
            }
            $current = $schedule->end;
        }

        if ($current < Carbon::parse($end)) {
            $freeSlots[] = [
                'start' => $current->toDateTimeString(),
                'end' => Carbon::parse($end)->toDateTimeString(),
            ];
        }

        return $freeSlots;
    }
}














