<?php
// File: database/migrations/2024_01_01_000006_create_user_permissions_table.php

namespace Database\Migrations;

use Illuminate\Support\Facades\Schema;

use Illuminate\Database\Schema\Blueprint;

class CreateUserPermissionsTable extends Migration
{
    public function up(): void
    {
        Schema::create('user_permissions', function (Blueprint $table) {
            $table->char('user_id', 36);
            $table->char('permission_id', 36);
            $table->boolean('is_granted')->default(true); // Can be used to explicitly deny
            $table->timestamp('granted_at')->useCurrent();
            $table->char('granted_by', 36)->nullable();
            $table->timestamp('expires_at')->nullable();
            
            $table->primary(['user_id', 'permission_id']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
            $table->foreign('granted_by')->references('id')->on('users')->onDelete('set null');
            
            $table->index('user_id');
            $table->index('permission_id');
            $table->index('expires_at');
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('user_permissions');
    }
}