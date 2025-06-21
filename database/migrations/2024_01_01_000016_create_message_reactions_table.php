<?php


// File: database/migrations/2024_01_01_000016_create_message_reactions_table.php

use Illuminate\Database\Schema\Blueprint;

class CreateMessageReactionsTable
{
    /**
     * Run the migrations.
     */
    public function up($schema)
    {
        $schema->create('message_reactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('message_id');
            $table->uuid('user_id');
            $table->string('emoji', 10);
            $table->timestamps();
            
            $table->foreign('message_id')->references('id')->on('messages')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->unique(['message_id', 'user_id', 'emoji']);
            $table->index('message_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down($schema)
    {
        $schema->dropIfExists('message_reactions');
    }
}
