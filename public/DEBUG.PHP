<?php
// File: public/check-logs.php
// Check various log files for errors

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Log File Analysis</h1>";

// PHP Error Log
echo "<h2>PHP Error Log</h2>";
$errorLog = ini_get('error_log');
if ($errorLog && file_exists($errorLog)) {
    $lines = array_slice(file($errorLog), -30); // Last 30 lines
    echo "<pre style='background: #f0f0f0; padding: 10px; overflow-x: auto;'>";
    foreach ($lines as $line) {
        if (stripos($line, 'error') !== false) {
            echo "<span style='color: red;'>" . e($line) . "</span>";
        } elseif (stripos($line, 'warning') !== false) {
            echo "<span style='color: orange;'>" . e($line) . "</span>";
        } else {
            echo e($line);
        }
    }
    echo "</pre>";
} else {
    echo "<p>PHP error log not found at: " . $errorLog . "</p>";
}

// Apache Error Log (Windows XAMPP typical location)
echo "<h2>Apache Error Log</h2>";
$apacheErrorLog = 'C:/xampp/apache/logs/error.log';
if (file_exists($apacheErrorLog)) {
    $lines = array_slice(file($apacheErrorLog), -20); // Last 20 lines
    echo "<pre style='background: #f0f0f0; padding: 10px; overflow-x: auto;'>";
    echo e(implode('', $lines));
    echo "</pre>";
} else {
    echo "<p>Apache error log not found at typical location</p>";
}

// Application Logs
echo "<h2>Application Logs</h2>";
$appLogDir = __DIR__ . '/../storage/logs';
if (is_dir($appLogDir)) {
    $logFiles = glob($appLogDir . '/*.log');
    if (!empty($logFiles)) {
        foreach ($logFiles as $logFile) {
            echo "<h3>" . basename($logFile) . "</h3>";
            $lines = array_slice(file($logFile), -20); // Last 20 lines
            echo "<pre style='background: #f0f0f0; padding: 10px; overflow-x: auto;'>";
            echo e(implode('', $lines));
            echo "</pre>";
        }
    } else {
        echo "<p>No log files found in application logs directory</p>";
    }
} else {
    echo "<p>Application logs directory not found</p>";
}

// Check for common issues
echo "<h2>Common Issues Check</h2>";

// Check if mod_rewrite is enabled
if (function_exists('apache_get_modules')) {
    $modules = apache_get_modules();
    if (in_array('mod_rewrite', $modules)) {
        echo "<p style='color: green;'>âœ“ mod_rewrite is enabled</p>";
    } else {
        echo "<p style='color: red;'>âœ— mod_rewrite is NOT enabled</p>";
    }
} else {
    echo "<p style='color: orange;'>âš  Cannot check Apache modules (apache_get_modules not available)</p>";
}

// Check .htaccess
$htaccess = __DIR__ . '/.htaccess';
if (file_exists($htaccess)) {
    echo "<p style='color: green;'>âœ“ .htaccess exists</p>";
    echo "<h3>.htaccess content:</h3>";
    echo "<pre style='background: #f0f0f0; padding: 10px;'>";
    echo e(file_get_contents($htaccess));
    echo "</pre>";
} else {
    echo "<p style='color: red;'>âœ— .htaccess NOT found</p>";
}

// Check for infinite redirects
echo "<h2>Testing for Redirect Loops</h2>";
echo "<p>If you're seeing endless loading, it's likely due to:</p>";
echo "<ul>";
echo "<li>Authentication middleware redirecting to /login</li>";
echo "<li>Login page also requiring authentication</li>";
echo "<li>Missing session initialization</li>";
echo "<li>Incorrect .htaccess rules</li>";
echo "</ul>";