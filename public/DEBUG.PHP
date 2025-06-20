<?php
// File: public/simple-test.php
// Simple test without dependency injection

// Use the bootstrap
require '../bootstrap/app.php';

echo "<h1>Simple Test</h1>";

// Test 1: Environment
echo "<h2>1. Environment Variables:</h2>";
echo "APP_NAME: " . ($_ENV['APP_NAME'] ?? 'Not set') . "<br>";
echo "APP_ENV: " . ($_ENV['APP_ENV'] ?? 'Not set') . "<br>";

// Test 2: Config
echo "<h2>2. Config Function:</h2>";
try {
    echo "App Name: " . config('app.name') . "<br>";
    echo "App Environment: " . config('app.env') . "<br>";
    echo "Session Lifetime: " . config('session.lifetime', 360) . "<br>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}

// Test 3: Session
echo "<h2>3. Session:</h2>";
if (isset($_SESSION['user'])) {
    echo "✅ User logged in: " . $_SESSION['user']['username'] . "<br>";
} else {
    echo "❌ Not logged in<br>";
}

// Test 4: Simple routing simulation
echo "<h2>4. Simple Dashboard Test:</h2>";
if (isset($_SESSION['user'])) {
    // Mock dashboard data
    $stats = [
        'totalEmployees' => 284,
        'presentToday' => 270,
        'onLeave' => 14,
        'newApplications' => 8
    ];
    
    echo "<div style='border: 1px solid #ccc; padding: 20px; margin: 10px 0;'>";
    echo "<h3>Dashboard Stats</h3>";
    foreach ($stats as $key => $value) {
        echo "$key: $value<br>";
    }
    echo "</div>";
    
    echo "<p>✅ Dashboard data loaded successfully!</p>";
    echo "<p><a href='dashboard'>Try full dashboard</a></p>";
} else {
    echo "<p>You need to be logged in to see dashboard</p>";
    echo "<p><a href='login-test.php'>Run login test first</a></p>";
}
?>