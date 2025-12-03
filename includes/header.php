<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$username = $_SESSION['username'] ?? '';
$isAdmin = isset($_SESSION['access_level']) && $_SESSION['access_level'] >= 100;
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'L2 Savior - Ultimate Lineage 2 Server'; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/animations.css">
</head>
<body>
    <nav>
        <div class="container">
            <div class="logo">L2 SAVIOR</div>
            <button class="nav-toggle" onclick="toggleMenu()">☰</button>
            <ul id="nav-menu">
                <li><a href="index.php">🏠 Home</a></li>
		<li><a href="news.php">📰 News</a></li>
                <li><a href="features.php">✨ Features</a></li>
                <li><a href="rankings.php">🏆 Rankings</a></li>
                <li><a href="download.php">⬇️ Download</a></li>
                
                <?php if ($isLoggedIn): ?>
                    <!-- Bejelentkezett user -->
                    <li><a href="shop.php">🛒 Shop</a></li>
                    <li><a href="account.php">👤 <?php echo htmlspecialchars($username); ?></a></li>
                    <?php if ($isAdmin): ?>
                        <li><a href="admin/index.php" style="color: #ff6b6b;">⚙️ Admin</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php">🚪 Logout</a></li>
                <?php else: ?>
                    <!-- Vendég -->
                    <li><a href="login.php">🔐 Login</a></li>
                    <li><a href="register.php">📝 Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
    
    <script>
    function toggleMenu() {
        const menu = document.getElementById('nav-menu');
        menu.classList.toggle('active');
    }
    document.addEventListener('click', function(e) {
        const menu = document.getElementById('nav-menu');
        const toggle = document.querySelector('.nav-toggle');
        if (menu && !menu.contains(e.target) && e.target !== toggle) {
            menu.classList.remove('active');
        }
    });
    </script>
