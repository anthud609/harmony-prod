<?php

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
            $table->string('preview', 255)->nullable();
            $table->boolean('is_read')->default(false);
            $table->boolean('is_archived')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->enum('type', ['personal', 'system', 'announcement', 'task'])->default('personal');
            $table->json('attachments')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('sender_id');
            $table->index('recipient_id');
            $table->index(['recipient_id', 'is_read']);
            $table->index(['recipient_id', 'is_archived']);
            $table->index(['recipient_id', 'is_read', 'is_archived']);
            $table->index('created_at');
            
            // Foreign keys
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('recipient_id')->references('id')->on('users')->onDelete('cascade');
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