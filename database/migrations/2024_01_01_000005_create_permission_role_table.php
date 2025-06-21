<?php

use Illuminate\Database\Schema\Blueprint;

class CreatePermissionRoleTable
{
    /**
     * Run the migrations.
     */
    public function up($schema)
    {
        $schema->create('permission_role', function (Blueprint $table) {
            $table->char('permission_id', 36);
            $table->char('role_id', 36);
            $table->timestamp('granted_at')->useCurrent();
            $table->char('granted_by', 36)->nullable();
            
            $table->primary(['permission_id', 'role_id']);
            $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            $table->foreign('granted_by')->references('id')->on('users')->onDelete('set null');
            
            $table->index('permission_id');
            $table->index('role_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down($schema)
    {
        $schema->dropIfExists('permission_role');
    }
}
