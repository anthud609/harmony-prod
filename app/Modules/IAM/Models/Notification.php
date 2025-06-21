<?php
// File: app/Modules/IAM/Models/Notification.php (Updated)

namespace App\Modules\IAM\Models;

use App\Core\Database\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends BaseModel
{
    use SoftDeletes;

    protected $table = 'notifications';

    protected $fillable = [
        'user_id',
        'type',
        'message',
        'data',
        'url',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    /**
     * Get the user that owns the notification
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(): void
    {
        if (!$this->is_read) {
            $this->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }
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
            
            // Document related
            'document_shared' => ['icon' => 'fas fa-share-alt', 'color' => 'indigo'],
            'document_uploaded' => ['icon' => 'fas fa-file-upload', 'color' => 'blue'],
            'policy_update' => ['icon' => 'fas fa-file-alt', 'color' => 'gray'],
            
            // Task related
            'task_assigned' => ['icon' => 'fas fa-tasks', 'color' => 'orange'],
            'task_completed' => ['icon' => 'fas fa-check-square', 'color' => 'green'],
            'task_overdue' => ['icon' => 'fas fa-exclamation-triangle', 'color' => 'red'],
            
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
     * Get time display
     */
    public function getTimeAttribute(): string
    {
        $now = now();
        $created = $this->created_at;
        
        if ($created->isToday()) {
            return $created->format('g:i A');
        } elseif ($created->isYesterday()) {
            return 'Yesterday at ' . $created->format('g:i A');
        } elseif ($created->diffInDays($now) < 7) {
            return $created->format('l \a\t g:i A'); // Day name
        } else {
            return $created->format('M j \a\t g:i A');
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
}