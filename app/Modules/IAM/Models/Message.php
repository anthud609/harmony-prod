<?php
// File: app/Modules/IAM/Models/Message.php

namespace App\Modules\IAM\Models;

use App\Core\Database\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends BaseModel
{
    protected $fillable = [
        'sender_id',
        'recipient_id',
        'subject',
        'body',
        'preview',
        'is_read',
        'is_archived',
        'read_at',
        'archived_at',
        'priority',
        'type',
        'attachments',
        'metadata'
    ];
    
    protected $casts = [
        'is_read' => 'boolean',
        'is_archived' => 'boolean',
        'read_at' => 'datetime',
        'archived_at' => 'datetime',
        'attachments' => 'array',
        'metadata' => 'array'
    ];
    
    protected $appends = ['time', 'preview', 'avatar'];
    
    /**
     * Message sender
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
    
    /**
     * Message recipient
     */
    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
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
        $time = $this->created_at;
        $diff = $now->getTimestamp() - $time->getTimestamp();

        if ($diff < 60) {
            return 'just now';
        } elseif ($diff < 3600) {
            $minutes = floor($diff / 60);
            return $minutes . 'm ago';
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $hours . 'h ago';
        } elseif ($diff < 604800) {
            $days = floor($diff / 86400);
            return $days . 'd ago';
        } else {
            return $time->format('M j');
        }
    }
    
    /**
     * Get message preview
     */
    public function getPreviewAttribute(): string
    {
        // If preview is already set, use it
        if (!empty($this->attributes['preview'])) {
            return $this->attributes['preview'];
        }
        
        // Otherwise generate from body
        $text = strip_tags($this->body ?? '');
        return substr($text, 0, 100) . (strlen($text) > 100 ? '...' : '');
    }
    
    /**
     * Get avatar data for sender
     */
    public function getAvatarAttribute(): array
    {
        if (!$this->sender) {
            return [
                'initials' => 'U',
                'gradient' => 'from-gray-400 to-gray-500'
            ];
        }
        
        return [
            'initials' => $this->sender->initials,
            'gradient' => $this->getAvatarGradient($this->sender->id)
        ];
    }
    
    /**
     * Get avatar gradient based on user ID
     */
    private function getAvatarGradient(string $userId): string
    {
        $gradients = [
            'from-green-400 to-blue-500',
            'from-purple-400 to-pink-500',
            'from-orange-400 to-red-500',
            'from-indigo-400 to-purple-500',
            'from-blue-400 to-cyan-500',
            'from-pink-400 to-red-500',
            'from-yellow-400 to-orange-500',
            'from-teal-400 to-green-500',
        ];
        
        // Use user ID to consistently pick a gradient
        $index = hexdec(substr(md5($userId), 0, 2)) % count($gradients);
        return $gradients[$index];
    }
    
    /**
     * Scope for unread messages
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }
    
    /**
     * Scope for archived messages
     */
    public function scopeArchived($query)
    {
        return $query->where('is_archived', true);
    }
    
    /**
     * Scope for inbox (not archived)
     */
    public function scopeInbox($query)
    {
        return $query->where('is_archived', false);
    }
    
    /**
     * Mark message as read
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
     * Archive message
     */
    public function archive(): bool
    {
        if (!$this->is_archived) {
            $this->is_archived = true;
            $this->archived_at = now();
            return $this->save();
        }
        return true;
    }
    
    /**
     * Unarchive message
     */
    public function unarchive(): bool
    {
        if ($this->is_archived) {
            $this->is_archived = false;
            $this->archived_at = null;
            return $this->save();
        }
        return true;
    }
}