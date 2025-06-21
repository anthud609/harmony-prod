<?php
// File: app/Modules/IAM/Models/Chat.php

namespace App\Modules\IAM\Models;

use App\Core\Database\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Chat extends BaseModel
{
    protected $table = 'chats';

    protected $fillable = [
        'type', // 'direct' or 'group'
        'name',
        'description',
        'avatar_url',
        'last_message_id',
        'last_message_at',
        'settings',
        'created_by',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'settings' => 'array',
    ];

    /**
     * Get participants
     */
    public function participants(): HasMany
    {
        return $this->hasMany(ChatParticipant::class);
    }

    /**
     * Get messages
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Get last message
     */
    public function lastMessage(): HasOne
    {
        return $this->hasOne(Message::class, 'id', 'last_message_id');
    }

    /**
     * Get users through participants
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'chat_participants')
                    ->withPivot(['role', 'is_pinned', 'is_muted', 'unread_count', 'joined_at', 'left_at'])
                    ->withTimestamps();
    }

    /**
     * Check if user is participant
     */
    public function hasParticipant(string $userId): bool
    {
        return $this->participants()->where('user_id', $userId)->exists();
    }

    /**
     * Add participant
     */
    public function addParticipant(string $userId, string $role = 'member'): ChatParticipant
    {
        return $this->participants()->create([
            'user_id' => $userId,
            'role' => $role,
            'joined_at' => now(),
        ]);
    }

    /**
     * Remove participant
     */
    public function removeParticipant(string $userId): bool
    {
        return $this->participants()
                    ->where('user_id', $userId)
                    ->update(['left_at' => now()]) > 0;
    }
}
