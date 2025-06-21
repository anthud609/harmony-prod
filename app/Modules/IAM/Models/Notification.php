<?php
namespace App\Modules\IAM\Models;

use App\Core\Database\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends BaseModel
{
    protected $fillable = [
        'user_id',
        'type',
        'message',
        'data',
        'is_read',
        'read_at',
        'url'
    ];
    
    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime'
    ];
    
    /**
     * Get the user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Get display data based on type
     */
    public function getDisplayDataAttribute(): array
    {
        $typeMap = [
            'leave_approved' => ['icon' => 'fas fa-check', 'color' => 'green'],
            'leave_rejected' => ['icon' => 'fas fa-times', 'color' => 'red'],
            'new_team_member' => ['icon' => 'fas fa-user-plus', 'color' => 'blue'],
            'birthday' => ['icon' => 'fas fa-birthday-cake', 'color' => 'purple'],
            'payroll_processed' => ['icon' => 'fas fa-money-check-alt', 'color' => 'green'],
            'document_uploaded' => ['icon' => 'fas fa-file-upload', 'color' => 'blue'],
            'meeting_scheduled' => ['icon' => 'fas fa-calendar-plus', 'color' => 'indigo'],
            'task_assigned' => ['icon' => 'fas fa-tasks', 'color' => 'orange'],
            'system_maintenance' => ['icon' => 'fas fa-tools', 'color' => 'yellow'],
            'security_alert' => ['icon' => 'fas fa-shield-alt', 'color' => 'red'],
            'default' => ['icon' => 'fas fa-info-circle', 'color' => 'gray']
        ];
        
        return $typeMap[$this->type] ?? $typeMap['default'];
    }
    
    /**
     * Get time display
     */
    public function getTimeAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }
}