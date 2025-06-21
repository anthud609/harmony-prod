<?php
// File: database/migrations/2024_01_01_000011_create_messages_table.php

use Illuminate\Database\Schema\Blueprint;

class CreateMessagesTable
{
    /**
     * Run the migrations.
     */
    public function up($schema)
    {
        $schema->create('messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('sender_id');
            $table->uuid('recipient_id');
            $table->string('subject')->nullable();
            $table->text('body');
            $table->text('preview')->nullable();
            $table->string('priority')->default('normal');
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->boolean('is_starred')->default(false);
            $table->boolean('is_archived')->default(false);
            $table->json('attachments')->nullable();
            $table->uuid('reply_to_id')->nullable();
            $table->uuid('thread_id')->nullable();
            $table->timestamps();
            $table->softDeletes(); // This adds the deleted_at column
            
            // Foreign keys
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('recipient_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('reply_to_id')->references('id')->on('messages')->onDelete('set null');
            
            // Indexes
            $table->index(['recipient_id', 'is_read']);
            $table->index(['sender_id']);
            $table->index('thread_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down($schema)
    {
        $schema->dropIfExists('messages');
    }
}