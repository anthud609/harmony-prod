<?php

// File: database/migrations/2024_01_01_000017_create_message_attachments_table.php

use Illuminate\Database\Schema\Blueprint;

class CreateMessageAttachmentsTable
{
    /**
     * Run the migrations.
     */
    public function up($schema)
    {
        $schema->create('message_attachments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('message_id');
            $table->enum('type', ['image', 'file', 'video', 'audio'])->default('file');
            $table->string('name');
            $table->string('url');
            $table->bigInteger('size')->unsigned(); // in bytes
            $table->string('mime_type')->nullable();
            $table->json('metadata')->nullable(); // dimensions for images, duration for videos, etc.
            $table->timestamps();
            
            $table->foreign('message_id')->references('id')->on('messages')->onDelete('cascade');
            $table->index('message_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down($schema)
    {
        $schema->dropIfExists('message_attachments');
    }
}