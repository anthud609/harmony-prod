<?php


// Update the existing Message model to include new relationships:

// File: app/Modules/IAM/Models/Message.php (Updated)

namespace App\Modules\IAM\Models;

use App\Core\Database\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends BaseModel
{
    use SoftDeletes;

    protected $table = 'messages';

    protected $fillable = [
        'chat_id',
        'sender_id',
        'reply_to_id',
        'body',
        'type', // 'text', 'image', 'file', 'system'
        'is_edited',
        'edited_at',
        'read_at',
        'delivered_at',
    ];

    protected $casts = [
        'is_edited' => 'boolean',
        'edited_at' => 'datetime',
        'read_at' => 'datetime',
        'delivered_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    /**
     * Get chat
     */
    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class);
    }

    /**
     * Get sender
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get message being replied to
     */
    public function replyTo(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'reply_to_id');
    }

    /**
     * Get reactions
     */
    public function reactions(): HasMany
    {
        return $this->hasMany(MessageReaction::class);
    }

    /**
     * Get attachments
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(MessageAttachment::class);
    }

    /**
     * Get preview text
     */
    public function getPreviewAttribute(): string
    {
        $text = strip_tags($this->body);
        if (strlen($text) > 100) {
            return substr($text, 0, 100) . '...';
        }
        return $text;
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
            return 'Yesterday';
        } elseif ($created->diffInDays($now) < 7) {
            return $created->format('l'); // Day name
        } else {
            return $created->format('M j');
        }
    }
/**
     * Get the recipient of the message
     */
    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }
    
    /**
     * Get the parent message (for replies)
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'parent_id');
    }
    
    /**
     * Get replies to this message
     */
    public function replies()
    {
        return $this->hasMany(Message::class, 'parent_id');
    }
    
    /**
     * Mark as read
     */
    public function markAsRead(): void
    {
        if (!$this->read_at) {
            $this->update(['read_at' => now()]);
        }
    }

    /**
     * Mark as delivered
     */
    public function markAsDelivered(): void
    {
        if (!$this->delivered_at) {
            $this->update(['delivered_at' => now()]);
        }
    }
}