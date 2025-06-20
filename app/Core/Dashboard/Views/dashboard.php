<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard – Harmony HRMS</title>
    <style>body{font-family:sans-serif;padding:2rem;}header{display:flex;justify-content:space-between;align-items:center;}nav a{margin-left:1rem;} </style>
</head>
<body>
    <header>
        <h1>Dashboard</h1>
        <nav>
            <span><?= htmlspecialchars($_SESSION['user']['username']) ?> (<?= htmlspecialchars($_SESSION['user']['role']) ?>)</span>
            <a href="/logout">Logout</a>
        </nav>
    </header>

    <main>
        <p>Welcome back, <?= htmlspecialchars($_SESSION['user']['username']) ?>!</p>
        <!-- add your dashboard widgets here -->
    </main>
</body>
</html>
