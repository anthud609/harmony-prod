<?php



// File: database/migrations/2024_01_01_000014_create_chat_participants_table.php

use Illuminate\Database\Schema\Blueprint;

class CreateChatParticipantsTable
{
    /**
     * Run the migrations.
     */
    public function up($schema)
    {
        $schema->create('chat_participants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('chat_id');
            $table->uuid('user_id');
            $table->enum('role', ['admin', 'member'])->default('member');
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_muted')->default(false);
            $table->integer('unread_count')->default(0);
            $table->timestamp('last_read_at')->nullable();
            $table->timestamp('joined_at');
            $table->timestamp('left_at')->nullable();
            $table->json('notification_settings')->nullable();
            $table->timestamps();
            
            $table->foreign('chat_id')->references('id')->on('chats')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->unique(['chat_id', 'user_id']);
            $table->index(['user_id', 'is_pinned']);
            $table->index(['user_id', 'unread_count']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down($schema)
    {
        $schema->dropIfExists('chat_participants');
    }
}
