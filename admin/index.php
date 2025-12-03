<?php
require_once '../php/admin_auth.php';

// Statisztikák lekérése
try {
    $pdoLS = getDBConnection(DB_LS);
    $pdoGS = getDBConnection(DB_GS);
    
    // Összes account
    $stmtAccounts = $pdoLS->query("SELECT COUNT(*) as total FROM accounts");
    $totalAccounts = $stmtAccounts->fetch()['total'];
    
    // Online játékosok
    $stmtOnline = $pdoGS->query("SELECT COUNT(*) as total FROM characters WHERE online = 1");
    $totalOnline = $stmtOnline->fetch()['total'];
    
    // Összes karakter
    $stmtChars = $pdoGS->query("SELECT COUNT(*) as total FROM characters");
    $totalCharacters = $stmtChars->fetch()['total'];
    
    // Bannolt accountok
    $stmtBanned = $pdoLS->query("SELECT COUNT(*) as total FROM accounts WHERE accessLevel < 0");
    $totalBanned = $stmtBanned->fetch()['total'];
    
    // Legutóbbi karakterek
    $stmtRecent = $pdoGS->query("
        SELECT char_name, level, account_name, online, createDate 
        FROM characters 
        ORDER BY createDate DESC 
        LIMIT 10
    ");
    $recentCharacters = $stmtRecent->fetchAll();
    
} catch (PDOException $e) {
    $error = "Hiba az adatok lekérésekor: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - L2 Savior</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .admin-container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid rgba(255, 215, 0, 0.2);
        }
        
        .admin-header h1 {
            background: linear-gradient(90deg, #ffd700, #50c878);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-size: 2rem;
            font-weight: 800;
        }
        
        .admin-nav {
            display: flex;
            gap: 1rem;
        }
        
        .admin-nav a {
            padding: 0.6rem 1.2rem;
            background: rgba(255, 215, 0, 0.1);
            border: 1px solid rgba(255, 215, 0, 0.3);
            border-radius: 6px;
            color: #ffd700;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .admin-nav a:hover {
            background: rgba(255, 215, 0, 0.2);
            transform: translateY(-2px);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }
        
        .stat-card {
            background: rgba(21, 21, 35, 0.8);
            border: 1px solid rgba(255, 215, 0, 0.2);
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
        }
        
        .stat-card h3 {
            color: #b8b8c8;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
        }
        
        .stat-card .value {
            font-size: 2.5rem;
            background: linear-gradient(90deg, #ffd700, #50c878);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 900;
        }
        
        .admin-section {
            background: rgba(21, 21, 35, 0.6);
            border: 1px solid rgba(255, 215, 0, 0.15);
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .admin-section h2 {
            color: #ffd700;
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .data-table th {
            background: rgba(255, 215, 0, 0.1);
            color: #ffd700;
            padding: 0.8rem;
            text-align: left;
            font-weight: 600;
            border-bottom: 1px solid rgba(255, 215, 0, 0.2);
        }
        
        .data-table td {
            padding: 0.8rem;
            color: #b8b8c8;
            border-bottom: 1px solid rgba(255, 215, 0, 0.05);
        }
        
        .data-table tr:hover {
            background: rgba(255, 215, 0, 0.05);
        }
        
        .badge-online {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            background: rgba(80, 200, 120, 0.2);
            color: #50c878;
            border-radius: 4px;
            font-size: 0.85rem;
        }
        
        .badge-offline {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            background: rgba(107, 114, 128, 0.2);
            color: #9ca3af;
            border-radius: 4px;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>
    <!-- Top Navigation -->
    <nav>
        <div class="container">
            <div class="logo">L2 SAVIOR - ADMIN</div>
            <ul>
    <li><a href="../index.php">🏠 Főoldal</a></li>
    <li><a href="index.php">📊 Dashboard</a></li>
    <li><a href="players.php">👥 Játékosok</a></li>
    <li><a href="news.php">📰 Hírek</a></li>
    <li><a href="shop_items.php">🛒 Shop</a></li>
    <li><a href="purchases.php">📦 Vásárlások</a></li>
    <li><a href="coin_management.php">💰 Coin Management</a></li>
    <li><a href="logout.php">🚪 Kilépés</a></li>
	</ul>
        </div>
    </nav>

    <!-- Admin Dashboard -->
    <div class="admin-container">
        <div class="admin-header">
            <h1>🛡️ Admin Dashboard</h1>
            <div style="color: #b8b8c8;">
                Bejelentkezve: <strong style="color: #ffd700;"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Ismeretlen'); ?> 
    (Level: <?php echo (int)($_SESSION['access_level'] ?? 0); ?>)
            </div>
        </div>

        <?php if (isset($error)): ?>
            <div style="background: rgba(220, 38, 38, 0.2); border: 1px solid rgba(220, 38, 38, 0.5); color: #fca5a5; padding: 1rem; border-radius: 8px; margin-bottom: 2rem;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Összes Account</h3>
                <div class="value"><?php echo number_format($totalAccounts ?? 0); ?></div>
            </div>
            <div class="stat-card">
                <h3>Online Játékosok</h3>
                <div class="value"><?php echo number_format($totalOnline ?? 0); ?></div>
            </div>
            <div class="stat-card">
                <h3>Összes Karakter</h3>
                <div class="value"><?php echo number_format($totalCharacters ?? 0); ?></div>
            </div>
            <div class="stat-card">
                <h3>Bannolt Accountok</h3>
                <div class="value"><?php echo number_format($totalBanned ?? 0); ?></div>
            </div>
        </div>

        <!-- Recent Characters -->
        <div class="admin-section">
            <h2>📝 Legutóbbi Karakterek</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Karakter név</th>
                        <th>Level</th>
                        <th>Account</th>
                        <th>Létrehozva</th>
                        <th>Státusz</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($recentCharacters)): ?>
                        <?php foreach ($recentCharacters as $char): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($char['char_name']); ?></strong></td>
                                <td><?php echo $char['level']; ?></td>
                                <td><?php echo htmlspecialchars($char['account_name']); ?></td>
                                <td><?php echo date('Y-m-d H:i', $char['createDate'] / 1000); ?></td>
                                <td>
                                    <?php if ($char['online']): ?>
                                        <span class="badge-online">🟢 Online</span>
                                    <?php else: ?>
                                        <span class="badge-offline">⚫ Offline</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 2rem;">Nincs adat</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Quick Actions -->
        <div class="admin-section">
            <h2>⚡ Gyors műveletek</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                <a href="players.php" class="btn btn-primary">👥 Játékosok kezelése</a>
                <a href="players.php?filter=online" class="btn btn-secondary">🟢 Online játékosok</a>
                <a href="players.php?filter=banned" class="btn btn-secondary">🚫 Bannolt accountok</a>
            </div>
        </div>

    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; 2025 L2 Savior - Admin Panel</p>
    </footer>
</body>
</html>
