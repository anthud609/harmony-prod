<?php
// File: app/Modules/IAM/Models/Message.php

namespace App\Modules\IAM\Models;

use App\Core\Database\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends BaseModel
{
    use SoftDeletes;
    
    protected $fillable = [
        'sender_id',
        'recipient_id',
        'subject',
        'body',
        'preview',
        'priority',
        'is_read',
        'read_at',
        'is_starred',
        'is_archived',
        'attachments',
        'reply_to_id',
        'thread_id'
    ];
    
    protected $casts = [
        'is_read' => 'boolean',
        'is_starred' => 'boolean',
        'is_archived' => 'boolean',
        'attachments' => 'array',
        'read_at' => 'datetime'
    ];
    
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
     * Parent message (for replies)
     */
    public function replyTo(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'reply_to_id');
    }
    
    /**
     * Message replies
     */
    public function replies(): HasMany
    {
        return $this->hasMany(Message::class, 'reply_to_id');
    }
    
    /**
     * Thread messages
     */
    public function thread(): HasMany
    {
        return $this->hasMany(Message::class, 'thread_id', 'thread_id');
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
            
            // Update recipient's message count
            $this->recipient->decrement('message_count');
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
            
            // Update recipient's message count
            $this->recipient->increment('message_count');
        }
    }
    
    /**
     * Toggle star status
     */
    public function toggleStar(): void
    {
        $this->update(['is_starred' => !$this->is_starred]);
    }
    
    /**
     * Archive message
     */
    public function archive(): void
    {
        $this->update(['is_archived' => true]);
    }
    
    /**
     * Unarchive message
     */
    public function unarchive(): void
    {
        $this->update(['is_archived' => false]);
    }
    
    /**
     * Scope for unread messages
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }
    
    /**
     * Scope for starred messages
     */
    public function scopeStarred($query)
    {
        return $query->where('is_starred', true);
    }
    
    /**
     * Scope for archived messages
     */
    public function scopeArchived($query)
    {
        return $query->where('is_archived', true);
    }
    
    /**
     * Scope for inbox (not archived, not deleted)
     */
    public function scopeInbox($query)
    {
        return $query->where('is_archived', false);
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
     * Get sender avatar data
     */
    public function getAvatarAttribute(): array
    {
        return [
            'initials' => $this->sender->initials,
            'gradient' => $this->getGradientForUser($this->sender_id)
        ];
    }
    
    /**
     * Get gradient color for user avatar
     */
    private function getGradientForUser(string $userId): string
    {
        $gradients = [
            'from-green-400 to-blue-500',
            'from-purple-400 to-pink-500',
            'from-orange-400 to-red-500',
            'from-yellow-400 to-green-500',
            'from-blue-400 to-indigo-500',
            'from-pink-400 to-purple-500'
        ];
        
        // Use user ID to consistently assign same gradient
        $index = crc32($userId) % count($gradients);
        return $gradients[$index];
    }
    
    /**
     * Generate preview from body
     */
    public function generatePreview(): void
    {
        $preview = strip_tags($this->body);
        $preview = str_replace(["\r\n", "\r", "\n"], ' ', $preview);
        $preview = preg_replace('/\s+/', ' ', $preview);
        $preview = trim($preview);
        
        if (strlen($preview) > 100) {
            $preview = substr($preview, 0, 97) . '...';
        }
        
        $this->preview = $preview;
    }
    
    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($message) {
            // Generate preview if not set
            if (empty($message->preview)) {
                $message->generatePreview();
            }
            
            // Set thread ID if this is a new thread
            if (empty($message->thread_id) && empty($message->reply_to_id)) {
                $message->thread_id = \Ramsey\Uuid\Uuid::uuid4()->toString();
            } elseif (!empty($message->reply_to_id) && empty($message->thread_id)) {
                // Get thread ID from parent message
                $parent = Message::find($message->reply_to_id);
                if ($parent) {
                    $message->thread_id = $parent->thread_id;
                }
            }
        });
        
        static::created(function ($message) {
            // Update recipient's message count
            if (!$message->is_read) {
                $message->recipient->increment('message_count');
            }
        });
    }
}