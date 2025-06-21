<?php
// File: database/migrations/2024_01_01_000013_create_chats_table.php

use Illuminate\Database\Schema\Blueprint;

class CreateChatsTable
{
    /**
     * Run the migrations.
     */
    public function up($schema)
    {
        $schema->create('chats', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('type', ['direct', 'group'])->default('direct');
            $table->string('name')->nullable(); // For group chats
            $table->text('description')->nullable();
            $table->string('avatar_url')->nullable();
            $table->uuid('last_message_id')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->json('settings')->nullable(); // Group settings, permissions, etc.
            $table->uuid('created_by')->nullable();
            $table->timestamps();
            
            $table->index('type');
            $table->index('last_message_at');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down($schema)
    {
        $schema->dropIfExists('chats');
    }
}