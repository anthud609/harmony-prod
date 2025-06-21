<?php

namespace App\Modules\IAM\Models;

use App\Core\Database\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends BaseModel
{
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'icon',
        'color',
        'is_read',
        'read_at',
        'expires_at'
    ];
    
    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'expires_at' => 'datetime'
    ];
    
    /**
     * User who owns this notification
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Mark as read
     */
    public function markAsRead(): void
    {
        if (!$this->is_read) {
            $this->update([
                'is_read' => true,
                'read_at' => now()
            ]);
        }
    }
    
    /**
     * Scope for unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }
    
    /**
     * Scope for recent notifications
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
    
    /**
     * Get formatted time
     */
    public function getTimeAttribute(): string
    {
        if (!$this->created_at) {
            return 'just now';
        }
        
        $diff = now()->diffInMinutes($this->created_at);
        
        if ($diff < 1) {
            return 'just now';
        } elseif ($diff < 60) {
            return $diff . ' minute' . ($diff > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 1440) {
            $hours = floor($diff / 60);
            return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 10080) {
            $days = floor($diff / 1440);
            return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
        } else {
            return $this->created_at->format('M j, Y');
        }
    }
    
    /**
     * Get icon and color based on type
     */
    public function getDisplayDataAttribute(): array
    {
        $defaults = [
            'leave_approved' => ['icon' => 'fas fa-check', 'color' => 'green'],
            'leave_rejected' => ['icon' => 'fas fa-times', 'color' => 'red'],
            'leave_pending' => ['icon' => 'fas fa-clock', 'color' => 'orange'],
            'new_team_member' => ['icon' => 'fas fa-user-plus', 'color' => 'blue'],
            'birthday' => ['icon' => 'fas fa-birthday-cake', 'color' => 'purple'],
            'announcement' => ['icon' => 'fas fa-bullhorn', 'color' => 'indigo'],
            'task_assigned' => ['icon' => 'fas fa-tasks', 'color' => 'yellow'],
            'payroll' => ['icon' => 'fas fa-money-check-alt', 'color' => 'green'],
            'system' => ['icon' => 'fas fa-cog', 'color' => 'gray'],
        ];
        
        return [
            'icon' => $this->icon ?? $defaults[$this->type]['icon'] ?? 'fas fa-bell',
            'color' => $this->color ?? $defaults[$this->type]['color'] ?? 'blue'
        ];
    }
}