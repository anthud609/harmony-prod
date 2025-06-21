<?php
// File: app/Modules/IAM/Models/ActivityLog.php

namespace App\Modules\IAM\Models;

use App\Core\Database\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends BaseModel
{
    protected $fillable = [
        'user_id',
        'type',
        'description',
        'subject_type',
        'subject_id',
        'properties',
        'ip_address',
        'user_agent',
        'session_id',
        'url',
        'method',
        'status_code'
    ];
    
    protected $casts = [
        'properties' => 'array'
    ];
    
    /**
     * User who performed the activity
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Get the subject of the activity
     */
    public function subject()
    {
        return $this->morphTo();
    }
    
    /**
     * Scope for filtering by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
    
    /**
     * Scope for filtering by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }
    
    /**
     * Scope for recent activities
     */
    public function scopeRecent($query, $days = 7)
    {
        $date = new \DateTime();
        $date->sub(new \DateInterval('P' . $days . 'D'));
        return $query->where('created_at', '>=', $date->format('Y-m-d H:i:s'));
    }
    
    /**
     * Create a log entry
     */
    public static function log(
        string $type,
        string $description,
        ?int $userId = null,
        array $properties = [],
        $subject = null
    ): self {
        // Get current user ID if not provided
        if ($userId === null && isset($_SESSION['user']['id'])) {
            $userId = $_SESSION['user']['id'];
        }
        
        return static::create([
            'user_id' => $userId,
            'type' => $type,
            'description' => $description,
            'properties' => $properties,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject ? $subject->id : null,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'session_id' => session_id() ?: null,
            'url' => $_SERVER['REQUEST_URI'] ?? null,
            'method' => $_SERVER['REQUEST_METHOD'] ?? null,
            'status_code' => http_response_code() ?: null
        ]);
    }
}