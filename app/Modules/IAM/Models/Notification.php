<?php
// File: app/Modules/IAM/Models/Notification.php

namespace App\Modules\IAM\Models;

use App\Core\Database\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends BaseModel
{
    protected $fillable = [
        'user_id',
        'type',
        'icon',
        'color',
        'title',
        'message',
        'data',
        'url',
        'is_read',
        'read_at',
        'is_important',
        'expires_at'
    ];
    
    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'is_important' => 'boolean',
        'read_at' => 'datetime',
        'expires_at' => 'datetime'
    ];
    
    /**
     * User who receives the notification
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
                'read_at' => new \DateTime()
            ]);
            
            // Update user's notification count
            $this->user->decrement('notification_count');
        }
    }
    
    /**
     * Mark as unread
     */
    public function markAsUnread(): void
    {
        if ($this->is_read) {
            $this->update([
                'is_read' => false,
                'read_at' => null
            ]);
            
            // Update user's notification count
            $this->user->increment('notification_count');
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
     * Scope for important notifications
     */
    public function scopeImportant($query)
    {
        return $query->where('is_important', true);
    }
    
    /**
     * Scope for non-expired notifications
     */
    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', date('Y-m-d H:i:s'));
        });
    }
    
    /**
     * Get formatted time
     */
    public function getTimeAttribute(): string
    {
        // Simple time difference calculation
        $created = $this->created_at;
        if (!$created) {
            return 'just now';
        }
        
        $now = new \DateTime();
        $diff = $now->getTimestamp() - $created->getTimestamp();
        
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
            return $created->format('M j, Y');
        }
    }
    
    /**
     * Get icon class based on type
     */
    public function getIconClassAttribute(): string
    {
        if ($this->icon) {
            return $this->icon;
        }
        
        $icons = [
            'leave_approved' => 'fas fa-check',
            'leave_rejected' => 'fas fa-times',
            'new_team_member' => 'fas fa-user-plus',
            'birthday' => 'fas fa-birthday-cake',
            'announcement' => 'fas fa-bullhorn',
            'task_assigned' => 'fas fa-tasks',
            'meeting_reminder' => 'fas fa-calendar',
            'system' => 'fas fa-info-circle'
        ];
        
        return $icons[$this->type] ?? 'fas fa-bell';
    }
    
    /**
     * Get color class based on type
     */
    public function getColorClassAttribute(): string
    {
        if ($this->color) {
            return $this->color;
        }
        
        $colors = [
            'leave_approved' => 'green',
            'leave_rejected' => 'red',
            'new_team_member' => 'blue',
            'birthday' => 'purple',
            'announcement' => 'yellow',
            'task_assigned' => 'indigo',
            'meeting_reminder' => 'orange',
            'system' => 'gray'
        ];
        
        return $colors[$this->type] ?? 'blue';
    }
}