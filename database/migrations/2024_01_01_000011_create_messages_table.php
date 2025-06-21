<?php
// File: database/migrations/2024_01_01_000011_create_messages_table.php

namespace Database\Migrations;

use Illuminate\Support\Facades\Schema;

use Illuminate\Database\Schema\Blueprint;

class CreateMessagesTable extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            $table->char('sender_id', 36)->index();
            $table->char('recipient_id', 36)->index();
            $table->string('subject', 255)->nullable();
            $table->text('content');
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
            
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('recipient_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->index(['recipient_id', 'is_read']);
            $table->index('created_at');
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
}