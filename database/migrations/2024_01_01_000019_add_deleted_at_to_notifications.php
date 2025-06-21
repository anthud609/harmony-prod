<?php
// File: database/migrations/2024_01_01_000019_add_deleted_at_to_notifications.php

use Illuminate\Database\Schema\Blueprint;

class AddDeletedAtToNotifications
{
    /**
     * Run the migrations.
     */
    public function up($schema)
    {
        $schema->table('notifications', function (Blueprint $table) {
            $table->softDeletes(); // Adds deleted_at column
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down($schema)
    {
        $schema->table('notifications', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
}