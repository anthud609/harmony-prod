<?php

use Illuminate\Database\Schema\Blueprint;

class CreateNotificationsTable
{
    /**
     * Run the migrations.
     */
    public function up($schema)
    {
        $schema->create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            $table->char('user_id', 36)->index();
            $table->string('type', 100);
            $table->string('icon', 50)->nullable();
            $table->string('color', 20)->nullable();
            $table->text('message');
            $table->json('data')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->index(['user_id', 'is_read']);
            $table->index('type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down($schema)
    {
        $schema->dropIfExists('notifications');
    }
}
