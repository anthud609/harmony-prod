<?php
// File: app/Modules/IAM/Models/Notification.php

namespace App\Modules\IAM\Models;

use App\Core\Database\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends BaseModel
{
    protected $table = 'notifications';
    
    // IMPORTANT: No soft deletes since the table doesn't have deleted_at column
    // If you need soft deletes later, add the column to the migration first
    
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
        'read_at' => 'datetime'
    ];
    
    /**
     * Get the user that owns the notification
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
            'leave_cancelled' => ['icon' => 'fas fa-ban', 'color' => 'gray'],
            
            // Meeting related
            'meeting_scheduled' => ['icon' => 'fas fa-calendar-plus', 'color' => 'indigo'],
            'meeting_reminder' => ['icon' => 'fas fa-bell', 'color' => 'blue'],
            'meeting_cancelled' => ['icon' => 'fas fa-calendar-times', 'color' => 'red'],
            
            // HR related
            'birthday' => ['icon' => 'fas fa-birthday-cake', 'color' => 'pink'],
            'anniversary' => ['icon' => 'fas fa-gift', 'color' => 'purple'],
            'new_team_member' => ['icon' => 'fas fa-user-plus', 'color' => 'blue'],
            
            // Payroll related
            'payroll_processed' => ['icon' => 'fas fa-money-check-alt', 'color' => 'green'],
            'expense_approved' => ['icon' => 'fas fa-receipt', 'color' => 'green'],
            'expense_rejected' => ['icon' => 'fas fa-receipt', 'color' => 'red'],
            
            // System related
            'system_maintenance' => ['icon' => 'fas fa-tools', 'color' => 'yellow'],
            'security_alert' => ['icon' => 'fas fa-shield-alt', 'color' => 'red'],
            'system_update' => ['icon' => 'fas fa-sync-alt', 'color' => 'blue'],
            
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
        if (!$this->created_at) {
            return 'just now';
        }

        $now = now();
        $diff = $now->diffInSeconds($this->created_at);

        if ($diff < 60) {
            return 'just now';
        } elseif ($diff < 3600) {
            return $this->created_at->diffInMinutes($now) . ' minute' . ($this->created_at->diffInMinutes($now) > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 86400) {
            return $this->created_at->diffInHours($now) . ' hour' . ($this->created_at->diffInHours($now) > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 604800) {
            return $this->created_at->diffInDays($now) . ' day' . ($this->created_at->diffInDays($now) > 1 ? 's' : '') . ' ago';
        } else {
            return $this->created_at->format('M j, Y');
        }
    }
    
    /**
     * Mark notification as read
     */
    public function markAsRead(): void
    {
        $this->update([
            'is_read' => true,
            'read_at' => now()
        ]);
    }
}