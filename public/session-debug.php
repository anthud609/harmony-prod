<?php
// File: public/session-test.php
// A simple page to test and debug session behavior

session_start();

// Get session info
$sessionId = session_id();
$sessionData = $_SESSION;
$lastActivity = $_SESSION['_last_activity'] ?? null;
$currentTime = time();
$elapsed = $lastActivity ? ($currentTime - $lastActivity) : 0;
$remaining = $lastActivity ? max(0, 360 - $elapsed) : 0;

?>
<!DOCTYPE html>
<html>
<head>
    <title>Session Debug</title>
    <style>
        body { font-family: monospace; padding: 20px; }
        .info { background: #f0f0f0; padding: 10px; margin: 10px 0; }
        .warning { background: #fff3cd; padding: 10px; margin: 10px 0; }
        .error { background: #f8d7da; padding: 10px; margin: 10px 0; }
        pre { background: #e9ecef; padding: 10px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>Session Debug Information</h1>
    
    <div class="info">
        <strong>Session ID:</strong> <?= htmlspecialchars($sessionId) ?><br>
        <strong>Current Time:</strong> <?= date('Y-m-d H:i:s', $currentTime) ?> (<?= $currentTime ?>)<br>
        <strong>Last Activity:</strong> <?= $lastActivity ? date('Y-m-d H:i:s', $lastActivity) . " ($lastActivity)" : 'Not set' ?><br>
        <strong>Elapsed Time:</strong> <?= $elapsed ?> seconds<br>
        <strong>Remaining Time:</strong> <?= $remaining ?> seconds<br>
        <strong>Session Lifetime:</strong> 360 seconds (6 minutes)<br>
        <strong>Warning Threshold:</strong> 60 seconds (1 minute)<br>
    </div>
    
    <?php if ($remaining <= 0 && $lastActivity): ?>
    <div class="error">
        <strong>⚠️ SESSION EXPIRED!</strong><br>
        Session should have been destroyed.
    </div>
    <?php elseif ($remaining <= 60 && $remaining > 0): ?>
    <div class="warning">
        <strong>⚠️ SESSION EXPIRING SOON!</strong><br>
        Warning should be displayed. <?= $remaining ?> seconds remaining.
    </div>
    <?php endif; ?>
    
    <h2>Session Data:</h2>
    <pre><?= print_r($sessionData, true) ?></pre>
    
    <h2>Session Configuration:</h2>
    <pre><?php
    echo "session.gc_maxlifetime: " . ini_get('session.gc_maxlifetime') . "\n";
    echo "session.cookie_lifetime: " . ini_get('session.cookie_lifetime') . "\n";
    echo "session.use_cookies: " . ini_get('session.use_cookies') . "\n";
    echo "session.use_only_cookies: " . ini_get('session.use_only_cookies') . "\n";
    echo "session.cookie_httponly: " . ini_get('session.cookie_httponly') . "\n";
    echo "session.cookie_secure: " . ini_get('session.cookie_secure') . "\n";
    echo "session.cookie_samesite: " . ini_get('session.cookie_samesite') . "\n";
    echo "session.save_path: " . session_save_path() . "\n";
    ?></pre>
    
    <h2>Actions:</h2>
    <button onclick="location.reload()">Refresh Page</button>
    <button onclick="testSessionStatus()">Test /api/session-status</button>
    <button onclick="extendSession()">Extend Session</button>
    
    <h2>API Response:</h2>
    <pre id="apiResponse">Click "Test /api/session-status" to see the response</pre>
    
    <script>
    async function testSessionStatus() {
        try {
            const response = await fetch('/api/session-status', {
                headers: {'X-Requested-With': 'XMLHttpRequest'}
            });
            const data = await response.json();
            document.getElementById('apiResponse').textContent = JSON.stringify(data, null, 2);
        } catch (error) {
            document.getElementById('apiResponse').textContent = 'Error: ' + error.message;
        }
    }
    
    async function extendSession() {
        try {
            const response = await fetch('/api/extend-session', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-Token': '<?= $_SESSION['csrf_tokens'][0]['token'] ?? '' ?>'
                }
            });
            const data = await response.json();
            alert('Session extended! Response: ' + JSON.stringify(data));
            location.reload();
        } catch (error) {
            alert('Error: ' + error.message);
        }
    }
    
    // Auto-refresh every 5 seconds
    setInterval(() => {
        document.title = `Session Debug - ${new Date().toLocaleTimeString()}`;
    }, 1000);
    </script>
</body>
</html>