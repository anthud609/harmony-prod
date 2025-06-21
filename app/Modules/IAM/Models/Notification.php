<?php
// File: app/Modules/IAM/Models/Notification.php

namespace App\Modules\IAM\Models;

use App\Core\Database\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends BaseModel
{
    use SoftDeletes;
    
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'url',
        'data',
        'is_read',
        'read_at',
        'priority',
        'expires_at'
    ];
    
    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
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
     * Scope for read notifications
     */
    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }
    
    /**
     * Scope for specific type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }
    
    /**
     * Scope for high priority
     */
    public function scopeHighPriority($query)
    {
        return $query->where('priority', 'high');
    }
    
    /**
     * Scope for not expired
     */
    public function scopeNotExpired($query)
    {
        return $query->where(function($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', new \DateTime());
        });
    }
    
    /**
     * Check if notification is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
    
    /**
     * Get display data for notification type
     */
    public function getDisplayDataAttribute(): array
    {
        $typeMap = [
            'leave_approved' => [
                'icon' => 'fas fa-check',
                'color' => 'green',
                'title' => 'Leave Approved'
            ],
            'leave_rejected' => [
                'icon' => 'fas fa-times',
                'color' => 'red',
                'title' => 'Leave Rejected'
            ],
            'new_team_member' => [
                'icon' => 'fas fa-user-plus',
                'color' => 'blue',
                'title' => 'New Team Member'
            ],
            'birthday' => [
                'icon' => 'fas fa-birthday-cake',
                'color' => 'purple',
                'title' => 'Birthday'
            ],
            'payroll_processed' => [
                'icon' => 'fas fa-money-check-alt',
                'color' => 'green',
                'title' => 'Payroll Processed'
            ],
            'document_uploaded' => [
                'icon' => 'fas fa-file-upload',
                'color' => 'blue',
                'title' => 'Document Uploaded'
            ],
            'meeting_scheduled' => [
                'icon' => 'fas fa-calendar-plus',
                'color' => 'indigo',
                'title' => 'Meeting Scheduled'
            ],
            'task_assigned' => [
                'icon' => 'fas fa-tasks',
                'color' => 'orange',
                'title' => 'Task Assigned'
            ],
            'system_maintenance' => [
                'icon' => 'fas fa-tools',
                'color' => 'yellow',
                'title' => 'System Maintenance'
            ],
            'security_alert' => [
                'icon' => 'fas fa-shield-alt',
                'color' => 'red',
                'title' => 'Security Alert'
            ],
            'default' => [
                'icon' => 'fas fa-info-circle',
                'color' => 'gray',
                'title' => 'Notification'
            ]
        ];

        return $typeMap[$this->type] ?? $typeMap['default'];
    }
    
    /**
     * Get formatted time for display
     */
    public function getTimeAttribute(): string
    {
        if (!$this->created_at) {
            return 'just now';
        }

        $now = new \DateTime();
        $diff = $now->getTimestamp() - $this->created_at->getTimestamp();

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
            return $this->created_at->format('M j, Y');
        }
    }
    
    /**
     * Create a notification for a user
     */
    public static function create(array $attributes = []): self
    {
        $notification = new static($attributes);
        $notification->save();
        
        // Increment user's notification count
        if ($notification->user_id) {
            User::where('id', $notification->user_id)
                ->increment('notification_count');
        }
        
        return $notification;
    }
    
    /**
     * Send notification to user
     */
    public static function send(
        string $userId,
        string $type,
        string $message,
        ?string $title = null,
        ?string $url = null,
        array $data = [],
        string $priority = 'normal',
        ?\DateTime $expiresAt = null
    ): self {
        return static::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'url' => $url,
            'data' => $data,
            'priority' => $priority,
            'expires_at' => $expiresAt
        ]);
    }
    
    /**
     * Send bulk notifications
     */
    public static function sendBulk(
        array $userIds,
        string $type,
        string $message,
        ?string $title = null,
        ?string $url = null,
        array $data = [],
        string $priority = 'normal'
    ): int {
        $notifications = [];
        $now = new \DateTime();
        
        foreach ($userIds as $userId) {
            $notifications[] = [
                'id' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
                'user_id' => $userId,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'url' => $url,
                'data' => json_encode($data),
                'priority' => $priority,
                'is_read' => false,
                'created_at' => $now->format('Y-m-d H:i:s'),
                'updated_at' => $now->format('Y-m-d H:i:s')
            ];
        }
        
        // Bulk insert
        static::insert($notifications);
        
        // Update notification counts
        User::whereIn('id', $userIds)->increment('notification_count');
        
        return count($notifications);
    }
}