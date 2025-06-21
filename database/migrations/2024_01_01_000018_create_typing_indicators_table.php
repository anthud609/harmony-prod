<?php



// File: database/migrations/2024_01_01_000018_create_typing_indicators_table.php

use Illuminate\Database\Schema\Blueprint;

class CreateTypingIndicatorsTable
{
    /**
     * Run the migrations.
     */
    public function up($schema)
    {
        $schema->create('typing_indicators', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('chat_id');
            $table->uuid('user_id');
            $table->timestamp('started_at');
            $table->timestamp('expires_at');
            
            $table->foreign('chat_id')->references('id')->on('chats')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->unique(['chat_id', 'user_id']);
            $table->index(['chat_id', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down($schema)
    {
        $schema->dropIfExists('typing_indicators');
    }
}