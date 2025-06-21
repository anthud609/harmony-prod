<?php
// File: database/migrate.php
// Run database migrations with proper Laravel bootstrapping

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../bootstrap/database.php';

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Capsule\Manager as Capsule;

// Ensure facades are properly set up
$capsule = Capsule::connection();
Schema::setConnection($capsule);

echo "Running migrations...\n";

// Get all migration files
$migrations = glob(__DIR__ . '/migrations/*.php');
sort($migrations);

// Track migrated files
$migratedFiles = [];

// Run each migration
foreach ($migrations as $file) {
    $filename = basename($file);
    
    // Skip the Migration base class
    if ($filename === 'Migration.php') {
        continue;
    }
    
    // Extract class name
    $className = 'Database\\Migrations\\' . str_replace('.php', '', preg_replace('/^\d{4}_\d{2}_\d{2}_\d{6}_/', '', $filename));
    $className = str_replace(' ', '', ucwords(str_replace('_', ' ', $className)));
    
    // Load the migration file
    require_once $file;
    
    if (!class_exists($className)) {
        echo "✗ Could not find class: $className\n";
        continue;
    }
    
    echo "Running migration: " . basename($className) . "...\n";
    
    try {
        $migration = new $className();
        $migration->up();
        echo "✓ " . basename($className) . " completed\n";
        $migratedFiles[] = $filename;
    } catch (Exception $e) {
        echo "✗ " . basename($className) . " failed: " . $e->getMessage() . "\n";
        echo "Migration failed. Stopping execution.\n";
        
        // Optionally rollback already run migrations
        if (!empty($migratedFiles)) {
            echo "\nRolling back completed migrations...\n";
            foreach (array_reverse($migratedFiles) as $migratedFile) {
                try {
                    $className = 'Database\\Migrations\\' . str_replace('.php', '', preg_replace('/^\d{4}_\d{2}_\d{2}_\d{6}_/', '', $migratedFile));
                    $className = str_replace(' ', '', ucwords(str_replace('_', ' ', $className)));
                    $migration = new $className();
                    if (method_exists($migration, 'down')) {
                        $migration->down();
                        echo "✓ Rolled back: " . basename($className) . "\n";
                    }
                } catch (Exception $rollbackError) {
                    echo "✗ Failed to rollback: " . basename($className) . " - " . $rollbackError->getMessage() . "\n";
                }
            }
        }
        
        exit(1);
    }
}

echo "\nAll migrations completed successfully!\n";

// Create database seeder if requested
if (in_array('--seed', $argv)) {
    echo "\nRunning database seeder...\n";
    require_once __DIR__ . '/seeders/DatabaseSeeder.php';
    
    try {
        $seeder = new Database\Seeders\DatabaseSeeder();
        $seeder->run();
        echo "✓ Database seeding completed\n";
    } catch (Exception $e) {
        echo "✗ Seeding failed: " . $e->getMessage() . "\n";
    }
}