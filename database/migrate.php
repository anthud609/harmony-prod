<?php
// File: database/migrate.php

require __DIR__ . '/../bootstrap/database.php';

$migrations = [
    \Database\Migrations\CreateUsersTable::class,
    \Database\Migrations\CreateRolesTable::class,
    \Database\Migrations\CreatePermissionsTable::class,
    \Database\Migrations\CreateRoleUserTable::class,
    \Database\Migrations\CreatePermissionRoleTable::class,
    \Database\Migrations\CreateUserPermissionsTable::class,
    \Database\Migrations\CreateSessionsTable::class,
    \Database\Migrations\CreateDepartmentsTable::class,
    \Database\Migrations\CreateActivityLogsTable::class,
    \Database\Migrations\CreateNotificationsTable::class,
    \Database\Migrations\CreateMessagesTable::class,
    \Database\Migrations\CreateMenuItemsTable::class
];

echo "Running migrations...\n";

foreach ($migrations as $migrationClass) {
    $migration = new $migrationClass();
    echo "Migrating: " . $migrationClass . "\n";
    
    try {
        $migration->up();
        echo "✓ Migrated successfully\n";
    } catch (\Exception $e) {
        echo "✗ Migration failed: " . $e->getMessage() . "\n";
        exit(1);
    }
}

echo "\nAll migrations completed!\n";

// Run seeder
echo "\nRunning seeder...\n";
$seeder = new \Database\Seeders\DatabaseSeeder();
$seeder->run();
echo "✓ Database seeded successfully!\n";