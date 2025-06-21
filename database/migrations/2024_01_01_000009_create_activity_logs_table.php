<?php

use Illuminate\Database\Schema\Blueprint;

class CreateActivityLogsTable
{
    /**
     * Run the migrations.
     */
    public function up($schema)
    {
        $schema->create('activity_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            $table->char('user_id', 36)->nullable()->index();
            $table->string('type', 100); // login, logout, create, update, delete, etc.
            $table->string('module', 100)->nullable(); // users, employees, etc.
            $table->string('action', 100); // specific action performed
            $table->text('description');
            $table->string('subject_type', 100)->nullable(); // Model class
            $table->char('subject_id', 36)->nullable(); // Model ID
            $table->json('properties')->nullable(); // Additional data
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('performed_at')->useCurrent();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            
            $table->index(['subject_type', 'subject_id']);
            $table->index('type');
            $table->index('module');
            $table->index('performed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down($schema)
    {
        $schema->dropIfExists('activity_logs');
    }
}
