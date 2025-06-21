<?php

// File: database/migrations/2024_01_01_000015_update_messages_table_for_chat.php

use Illuminate\Database\Schema\Blueprint;

class UpdateMessagesTableForChat
{
    /**
     * Run the migrations.
     */
    public function up($schema)
    {
        $schema->table('messages', function (Blueprint $table) {
            // Add chat support
            $table->uuid('chat_id')->nullable()->after('id');
            $table->uuid('reply_to_id')->nullable()->after('sender_id');
            $table->enum('type', ['text', 'image', 'file', 'system'])->default('text')->after('body');
            $table->boolean('is_edited')->default(false)->after('type');
            $table->timestamp('edited_at')->nullable()->after('is_edited');
            $table->timestamp('delivered_at')->nullable()->after('read_at');
            $table->softDeletes();
            
            // Update recipient_id to be nullable for group chats
            $table->uuid('recipient_id')->nullable()->change();
            
            // Add indexes
            $table->index('chat_id');
            $table->index(['chat_id', 'created_at']);
            $table->foreign('chat_id')->references('id')->on('chats')->onDelete('cascade');
            $table->foreign('reply_to_id')->references('id')->on('messages')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down($schema)
    {
        $schema->table('messages', function (Blueprint $table) {
            $table->dropForeign(['chat_id']);
            $table->dropForeign(['reply_to_id']);
            $table->dropColumn(['chat_id', 'reply_to_id', 'type', 'is_edited', 'edited_at', 'delivered_at']);
            $table->dropSoftDeletes();
            
            // Make recipient_id required again
            $table->uuid('recipient_id')->nullable(false)->change();
        });
    }
}