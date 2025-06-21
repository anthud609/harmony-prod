<?php
// File: fix-schema-calls.php
// Fix Schema facade usage in migrations

$migrationsPath = __DIR__ . '/database/migrations/';
$files = glob($migrationsPath . '*.php');

echo "Fixing Schema facade usage in migration files...\n\n";

foreach ($files as $file) {
    if (basename($file) === 'Migration.php') {
        continue; // Skip the base class
    }
    
    $content = file_get_contents($file);
    $originalContent = $content;
    
    // Add the Schema facade import if not present
    if (!preg_match('/use\s+Illuminate\\\\Support\\\\Facades\\\\Schema;/', $content)) {
        // Add after the namespace declaration
        $content = preg_replace(
            '/(namespace[^;]+;)/',
            "$1\n\nuse Illuminate\\Support\\Facades\\Schema;",
            $content
        );
    }
    
    // Replace $this->schema-> with Schema::
    $content = preg_replace('/\$this->schema->/', 'Schema::', $content);
    
    // Also ensure we have the Blueprint import
    if (!preg_match('/use\s+Illuminate\\\\Database\\\\Schema\\\\Blueprint;/', $content)) {
        // Add after the Schema import
        $content = preg_replace(
            '/(use\s+Illuminate\\\\Support\\\\Facades\\\\Schema;)/',
            "$1\nuse Illuminate\\Database\\Schema\\Blueprint;",
            $content
        );
    }
    
    // Check if file was modified
    if ($content !== $originalContent) {
        file_put_contents($file, $content);
        echo "✓ Fixed: " . basename($file) . "\n";
    } else {
        echo "  No changes needed: " . basename($file) . "\n";
    }
}

echo "\nChecking first migration file for any remaining issues...\n";

// Let's also check and show the first few lines of a migration to ensure it's correct
$firstMigration = $migrationsPath . '2024_01_01_000001_create_users_table.php';
if (file_exists($firstMigration)) {
    $content = file_get_contents($firstMigration);
    $lines = explode("\n", $content);
    echo "\nFirst 20 lines of CreateUsersTable migration:\n";
    echo "=" . str_repeat("=", 60) . "\n";
    for ($i = 0; $i < min(20, count($lines)); $i++) {
        echo sprintf("%2d: %s\n", $i + 1, $lines[$i]);
    }
    echo "=" . str_repeat("=", 60) . "\n";
}

echo "\nAll Schema calls have been fixed!\n";
echo "You can now run: php database/migrate.php\n";