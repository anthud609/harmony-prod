<?php
// File: database/migrate.php

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/bootstrap/database.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

// First, require the base Migration class
require_once __DIR__ . '/migrations/Migration.php';

// Function to run a migration
function runMigration($migrationFile, $className)
{
    require_once $migrationFile;
    
    // Use the correct namespace
    $fullClassName = "Database\\Migrations\\{$className}";
    
    if (!class_exists($fullClassName)) {
        throw new Exception("Migration class {$fullClassName} not found in {$migrationFile}");
    }
    
    $migration = new $fullClassName();
    
    echo "Running migration: {$className}...\n";
    
    try {
        $migration->up();
        echo "✓ {$className} completed successfully\n";
    } catch (Exception $e) {
        echo "✗ {$className} failed: " . $e->getMessage() . "\n";
        throw $e;
    }
}

// Main execution
echo "Running migrations...\n\n";

$migrationsPath = __DIR__ . '/migrations';
$migrationFiles = glob($migrationsPath . '/*.php');

// Sort files to ensure they run in order
sort($migrationFiles);

$successCount = 0;
$failCount = 0;

foreach ($migrationFiles as $file) {
    // Skip the base Migration.php file
    if (basename($file) === 'Migration.php') {
        continue;
    }
    
    // Extract class name from filename
    // Format: 2024_01_01_000001_create_users_table.php -> CreateUsersTable
    $filename = basename($file, '.php');
    $parts = explode('_', $filename);
    
    // Remove date parts (first 4 parts)
    $nameParts = array_slice($parts, 4);
    
    // Convert to PascalCase
    $className = '';
    foreach ($nameParts as $part) {
        $className .= ucfirst($part);
    }
    
    try {
        runMigration($file, $className);
        $successCount++;
    } catch (Exception $e) {
        $failCount++;
        echo "\nMigration failed. Stopping execution.\n";
        exit(1);
    }
}

echo "\n";
echo "Migration summary:\n";
echo "✓ Successful: {$successCount}\n";
echo "✗ Failed: {$failCount}\n";

if ($failCount === 0) {
    echo "\nAll migrations completed successfully!\n";
}