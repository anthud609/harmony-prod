<?php

// File: app/Modules/IAM/Models/MessageReaction.php

namespace App\Modules\IAM\Models;

use App\Core\Database\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageReaction extends BaseModel
{
    protected $table = 'message_reactions';

    protected $fillable = [
        'message_id',
        'user_id',
        'emoji',
    ];

    /**
     * Get message
     */
    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }

    /**
     * Get user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}