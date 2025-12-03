<?php
require_once '../php/admin_auth.php';

// Szűrő és keresés
$filter = $_GET['filter'] ?? 'all';
$search = $_GET['search'] ?? '';

try {
    $pdoLS = getDBConnection(DB_LS);
    
    // Lekérdezés építése
    $sql = "SELECT login, accessLevel, lastServer, lastIP, lastactive FROM accounts WHERE 1=1";
    $params = [];
    
    if ($filter === 'banned') {
        $sql .= " AND accessLevel < 0";
    } elseif ($filter === 'active') {
        $sql .= " AND lastactive > ?";
        $params[] = (time() - 86400) * 1000; // utolsó 24 óra
    }
    
    if (!empty($search)) {
        $sql .= " AND login LIKE ?";
        $params[] = '%' . $search . '%';
    }
    
    $sql .= " ORDER BY lastactive DESC LIMIT 50";
    
    $stmt = $pdoLS->prepare($sql);
    $stmt->execute($params);
    $accounts = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error = "Hiba: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Játékosok - Admin Panel</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .admin-container { max-width: 1400px; margin: 2rem auto; padding: 0 2rem; }
        .admin-header { margin-bottom: 2rem; }
        .admin-header h1 { background: linear-gradient(90deg, #ffd700, #50c878); -webkit-background-clip: text; -webkit-text-fill-color: transparent; font-size: 2rem; font-weight: 800; }
        .filter-bar { display: flex; gap: 1rem; margin-bottom: 2rem; flex-wrap: wrap; }
        .filter-bar select, .filter-bar input { padding: 0.6rem; background: rgba(21, 21, 35, 0.8); border: 1px solid rgba(255, 215, 0, 0.3); border-radius: 6px; color: #b8b8c8; }
        .filter-bar button { padding: 0.6rem 1.5rem; background: #ffd700; color: #0f0f1a; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; }
        .data-table { width: 100%; border-collapse: collapse; background: rgba(21, 21, 35, 0.6); border-radius: 12px; overflow: hidden; }
        .data-table th { background: rgba(255, 215, 0, 0.1); color: #ffd700; padding: 1rem; text-align: left; }
        .data-table td { padding: 1rem; color: #b8b8c8; border-bottom: 1px solid rgba(255, 215, 0, 0.05); }
        .data-table tr:hover { background: rgba(255, 215, 0, 0.05); }
        .btn-ban { padding: 0.4rem 0.8rem; background: rgba(220, 38, 38, 0.2); color: #dc2626; border: 1px solid rgba(220, 38, 38, 0.5); border-radius: 4px; text-decoration: none; font-size: 0.85rem; }
        .btn-unban { padding: 0.4rem 0.8rem; background: rgba(34, 197, 94, 0.2); color: #22c55e; border: 1px solid rgba(34, 197, 94, 0.5); border-radius: 4px; text-decoration: none; font-size: 0.85rem; }
        .badge-banned { display: inline-block; padding: 0.3rem 0.8rem; background: rgba(220, 38, 38, 0.2); color: #dc2626; border-radius: 4px; font-size: 0.85rem; }
        .badge-normal { display: inline-block; padding: 0.3rem 0.8rem; background: rgba(80, 200, 120, 0.2); color: #50c878; border-radius: 4px; font-size: 0.85rem; }
    </style>
</head>
<body>
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

    <div class="admin-container">
        <div class="admin-header">
            <h1>👥 Játékosok kezelése</h1>
        </div>

        <!-- Filter Bar -->
        <form method="GET" class="filter-bar">
            <select name="filter" onchange="this.form.submit()">
                <option value="all" <?php echo $filter === 'all' ? 'selected' : ''; ?>>Összes</option>
                <option value="active" <?php echo $filter === 'active' ? 'selected' : ''; ?>>Aktívak (24h)</option>
                <option value="banned" <?php echo $filter === 'banned' ? 'selected' : ''; ?>>Bannoltak</option>
            </select>
            <input type="text" name="search" placeholder="Keresés felhasználónévre..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit">🔍 Keresés</button>
        </form>

        <!-- Players Table -->
        <table class="data-table">
            <thead>
                <tr>
                    <th>Felhasználónév</th>
                    <th>Access Level</th>
                    <th>Utolsó IP</th>
                    <th>Utolsó aktivitás</th>
                    <th>Státusz</th>
                    <th>Műveletek</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($accounts)): ?>
                    <?php foreach ($accounts as $acc): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($acc['login']); ?></strong></td>
                            <td><?php echo $acc['accessLevel']; ?></td>
                            <td><?php echo htmlspecialchars($acc['lastIP'] ?? 'N/A'); ?></td>
                            <td>
                                <?php 
                                if ($acc['lastactive']) {
                                    echo date('Y-m-d H:i', $acc['lastactive'] / 1000);
                                } else {
                                    echo 'Soha';
                                }
                                ?>
                            </td>
                            <td>
                                <?php if ($acc['accessLevel'] < 0): ?>
                                    <span class="badge-banned">🚫 Bannolva</span>
                                <?php else: ?>
                                    <span class="badge-normal">✅ Normál</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($acc['accessLevel'] < 0): ?>
                                    <a href="ban_player.php?action=unban&username=<?php echo urlencode($acc['login']); ?>" class="btn-unban" onclick="return confirm('Biztos fel akarod oldani a bant?')">
                                        Unban
                                    </a>
                                <?php else: ?>
                                    <a href="ban_player.php?action=ban&username=<?php echo urlencode($acc['login']); ?>" class="btn-ban" onclick="return confirm('Biztos bannolni akarod?')">
                                        Ban
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 2rem;">Nincs találat</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <footer>
        <p>&copy; 2025 L2 Savior - Admin Panel</p>
    </footer>
</body>
</html>
