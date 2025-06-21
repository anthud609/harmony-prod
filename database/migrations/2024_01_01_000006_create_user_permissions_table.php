<?php

use Illuminate\Database\Schema\Blueprint;

class CreateUserPermissionsTable
{
    /**
     * Run the migrations.
     */
    public function up($schema)
    {
        $schema->create('user_permissions', function (Blueprint $table) {
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

    /**
     * Reverse the migrations.
     */
    public function down($schema)
    {
        $schema->dropIfExists('user_permissions');
    }
}
