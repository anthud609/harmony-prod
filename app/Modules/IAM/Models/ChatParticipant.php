<?php

// File: app/Modules/IAM/Models/ChatParticipant.php

namespace App\Modules\IAM\Models;

use App\Core\Database\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatParticipant extends BaseModel
{
    protected $table = 'chat_participants';

    protected $fillable = [
        'chat_id',
        'user_id',
        'role', // 'admin', 'member'
        'is_pinned',
        'is_muted',
        'unread_count',
        'last_read_at',
        'joined_at',
        'left_at',
        'notification_settings',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'is_muted' => 'boolean',
        'unread_count' => 'integer',
        'last_read_at' => 'datetime',
        'joined_at' => 'datetime',
        'left_at' => 'datetime',
        'notification_settings' => 'array',
    ];

    /**
     * Get chat
     */
    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class);
    }

    /**
     * Get user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if participant is active
     */
    public function isActive(): bool
    {
        return is_null($this->left_at);
    }

    /**
     * Mark as read
     */
    public function markAsRead(): void
    {
        $this->update([
            'unread_count' => 0,
            'last_read_at' => now(),
        ]);
    }

    /**
     * Increment unread count
     */
    public function incrementUnread(): void
    {
        $this->increment('unread_count');
    }
}
