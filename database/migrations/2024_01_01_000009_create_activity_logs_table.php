<?php
// File: database/migrations/2024_01_01_000009_create_activity_logs_table.php

namespace Database\Migrations;

use Illuminate\Support\Facades\Schema;

use Illuminate\Database\Schema\Blueprint;

class CreateActivityLogsTable extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
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
    
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
}