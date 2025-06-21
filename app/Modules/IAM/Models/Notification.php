<?php
// File: app/Modules/IAM/Models/Notification.php

namespace App\Modules\IAM\Models;

use App\Core\Database\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends BaseModel
{
    /**
     * The table associated with the model.
     * IMPORTANT: This ensures we're using the notifications table, not messages
     */
    protected $table = 'notifications';
    
    protected $fillable = [
        'user_id',
        'type',
        'message',
        'data',
        'url',
        'is_read',
        'read_at'
    ];
    
    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
    
    /**
     * The attributes that should be hidden for arrays.
     */
    protected $hidden = [
        'data', // Hide raw data from API responses
        'deleted_at'
    ];
    
    /**
     * The attributes that should be appended to arrays.
     */
    protected $appends = [
        'display_data',
        'time'
    ];
    
    /**
     * User who receives the notification
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Get display data based on notification type
     */
    public function getDisplayDataAttribute(): array
    {
        $typeMap = [
            // Leave related
            'leave_approved' => ['icon' => 'fas fa-check-circle', 'color' => 'green'],
            'leave_rejected' => ['icon' => 'fas fa-times-circle', 'color' => 'red'],
            'leave_pending' => ['icon' => 'fas fa-clock', 'color' => 'orange'],
            
            // Meeting related
            'meeting_scheduled' => ['icon' => 'fas fa-calendar-plus', 'color' => 'indigo'],
            'meeting_reminder' => ['icon' => 'fas fa-bell', 'color' => 'blue'],
            
            // HR related
            'birthday' => ['icon' => 'fas fa-birthday-cake', 'color' => 'pink'],
            'anniversary' => ['icon' => 'fas fa-gift', 'color' => 'purple'],
            'new_team_member' => ['icon' => 'fas fa-user-plus', 'color' => 'blue'],
            
            // Payroll related
            'payroll_processed' => ['icon' => 'fas fa-money-check-alt', 'color' => 'green'],
            
            // Default
            'default' => ['icon' => 'fas fa-info-circle', 'color' => 'gray']
        ];

        return $typeMap[$this->type] ?? $typeMap['default'];
    }
    
    /**
     * Get formatted time
     */
    public function getTimeAttribute(): string
    {
        $now = new \DateTime();
        $time = $this->created_at;
        $diff = $now->getTimestamp() - $time->getTimestamp();

        if ($diff < 60) {
            return 'just now';
        } elseif ($diff < 3600) {
            $minutes = floor($diff / 60);
            return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 604800) {
            $days = floor($diff / 86400);
            return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
        } else {
            return $time->format('M j, Y');
        }
    }
    
    /**
     * Mark notification as read
     */
    public function markAsRead(): bool
    {
        if (!$this->is_read) {
            $this->is_read = true;
            $this->read_at = now();
            return $this->save();
        }
        return true;
    }
    
    /**
     * Scope for unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }
    
    /**
     * Scope for read notifications
     */
    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }
    
    /**
     * Scope for recent notifications
     */
    public function scopeRecent($query, $days = 7)
    {
        $date = now()->subDays($days);
        return $query->where('created_at', '>=', $date);
    }
}