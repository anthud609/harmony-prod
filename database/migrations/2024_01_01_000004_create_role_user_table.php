<?php

use Illuminate\Database\Schema\Blueprint;

class CreateRoleUserTable
{
    /**
     * Run the migrations.
     */
    public function up($schema)
    {
        $schema->create('role_user', function (Blueprint $table) {
            $table->char('role_id', 36);
            $table->char('user_id', 36);
            $table->timestamp('assigned_at')->useCurrent();
            $table->char('assigned_by', 36)->nullable();
            
            $table->primary(['role_id', 'user_id']);
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('assigned_by')->references('id')->on('users')->onDelete('set null');
            
            $table->index('role_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down($schema)
    {
        $schema->dropIfExists('role_user');
    }
}
