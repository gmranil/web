<?php
require_once '../php/admin_auth.php';

// Szűrés
$statusFilter = $_GET['status'] ?? 'all';

// Vásárlások lekérése
try {
    $pdo = getDBConnection(DB_GS);
    
    if ($statusFilter === 'all') {
        $stmt = $pdo->query("SELECT * FROM shop_purchases ORDER BY purchased_at DESC LIMIT 100");
    } else {
        $stmt = $pdo->prepare("SELECT * FROM shop_purchases WHERE status = ? ORDER BY purchased_at DESC LIMIT 100");
        $stmt->execute([$statusFilter]);
    }
    
    $purchases = $stmt->fetchAll();
    
    // Statisztikák
    $stmtStats = $pdo->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered,
            SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
        FROM shop_purchases
    ");
    $stats = $stmtStats->fetch();
    
} catch (PDOException $e) {
    $purchases = [];
    $error = "Hiba: " . $e->getMessage();
}

$success = $_SESSION['success'] ?? null;
$errorMsg = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vásárlások kezelése - Admin Panel</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .admin-container { max-width: 1400px; margin: 2rem auto; padding: 0 2rem; }
        .admin-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem; }
        .admin-header h1 { background: linear-gradient(90deg, #ffd700, #50c878); -webkit-background-clip: text; -webkit-text-fill-color: transparent; font-size: 2rem; font-weight: 800; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
        .stat-card { background: rgba(21, 21, 35, 0.8); border: 1px solid rgba(255, 215, 0, 0.2); border-radius: 8px; padding: 1.5rem; text-align: center; }
        .stat-card .number { font-size: 2.5rem; font-weight: 900; background: linear-gradient(90deg, #ffd700, #50c878); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .stat-card .label { color: #b8b8c8; font-size: 0.9rem; margin-top: 0.5rem; }
        .filter-tabs { display: flex; gap: 1rem; margin-bottom: 2rem; flex-wrap: wrap; }
        .filter-tab { padding: 0.6rem 1.5rem; background: rgba(21, 21, 35, 0.6); border: 1px solid rgba(255, 215, 0, 0.2); border-radius: 6px; color: #b8b8c8; text-decoration: none; transition: all 0.3s; }
        .filter-tab:hover, .filter-tab.active { background: rgba(255, 215, 0, 0.2); border-color: #ffd700; color: #ffd700; }
        .data-table { width: 100%; border-collapse: collapse; background: rgba(21, 21, 35, 0.6); border-radius: 12px; overflow: hidden; }
        .data-table th { background: rgba(255, 215, 0, 0.1); color: #ffd700; padding: 1rem; text-align: left; }
        .data-table td { padding: 1rem; color: #b8b8c8; border-bottom: 1px solid rgba(255, 215, 0, 0.05); }
        .data-table tr:hover { background: rgba(255, 215, 0, 0.05); }
        .btn-small { padding: 0.4rem 0.8rem; border-radius: 4px; text-decoration: none; font-size: 0.85rem; border: none; cursor: pointer; }
        .btn-deliver { background: rgba(34, 197, 94, 0.2); color: #50c878; border: 1px solid rgba(34, 197, 94, 0.5); }
        .btn-cancel { background: rgba(220, 38, 38, 0.2); color: #dc2626; border: 1px solid rgba(220, 38, 38, 0.5); }
        .status-pending { color: #fbbf24; }
        .status-delivered { color: #50c878; }
        .status-cancelled { color: #dc2626; }
        .alert-success { background: rgba(34, 197, 94, 0.2); border: 1px solid rgba(34, 197, 94, 0.5); color: #86efac; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; }
        .alert-error { background: rgba(220, 38, 38, 0.2); border: 1px solid rgba(220, 38, 38, 0.5); color: #fca5a5; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; }
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
            <h1>📦 Vásárlások kezelése</h1>
        </div>

        <?php if ($success): ?>
            <div class="alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if ($errorMsg): ?>
            <div class="alert-error"><?php echo htmlspecialchars($errorMsg); ?></div>
        <?php endif; ?>

        <!-- Stats -->
        <?php if (isset($stats)): ?>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="number"><?php echo number_format($stats['total']); ?></div>
                <div class="label">Összes vásárlás</div>
            </div>
            <div class="stat-card">
                <div class="number" style="background: linear-gradient(90deg, #fbbf24, #f59e0b); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                    <?php echo number_format($stats['pending']); ?>
                </div>
                <div class="label">Függőben</div>
            </div>
            <div class="stat-card">
                <div class="number" style="background: linear-gradient(90deg, #50c878, #34d399); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                    <?php echo number_format($stats['delivered']); ?>
                </div>
                <div class="label">Kézbesítve</div>
            </div>
            <div class="stat-card">
                <div class="number" style="background: linear-gradient(90deg, #dc2626, #ef4444); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                    <?php echo number_format($stats['cancelled']); ?>
                </div>
                <div class="label">Visszavonva</div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Filter Tabs -->
        <div class="filter-tabs">
            <a href="purchases.php?status=all" class="filter-tab <?php echo $statusFilter === 'all' ? 'active' : ''; ?>">
                Összes
            </a>
            <a href="purchases.php?status=pending" class="filter-tab <?php echo $statusFilter === 'pending' ? 'active' : ''; ?>">
                ⏳ Függőben
            </a>
            <a href="purchases.php?status=delivered" class="filter-tab <?php echo $statusFilter === 'delivered' ? 'active' : ''; ?>">
                ✅ Kézbesítve
            </a>
            <a href="purchases.php?status=cancelled" class="filter-tab <?php echo $statusFilter === 'cancelled' ? 'active' : ''; ?>">
                ❌ Visszavonva
            </a>
        </div>

        <!-- Purchases Table -->
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Felhasználó</th>
                    <th>Item</th>
                    <th>Ár</th>
                    <th>Státusz</th>
                    <th>Vásárlás</th>
                    <th>Kézbesítés</th>
                    <th>Műveletek</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($purchases)): ?>
                    <?php foreach ($purchases as $purchase): ?>
                        <tr>
                            <td>#<?php echo $purchase['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($purchase['username']); ?></strong></td>
                            <td><?php echo htmlspecialchars($purchase['item_name']); ?></td>
                            <td style="color: #ffd700;"><?php echo number_format($purchase['price']); ?> DC</td>
                            <td>
                                <?php if ($purchase['status'] === 'delivered'): ?>
                                    <span class="status-delivered">✅ Kézbesítve</span>
                                <?php elseif ($purchase['status'] === 'pending'): ?>
                                    <span class="status-pending">⏳ Függőben</span>
                                <?php else: ?>
                                    <span class="status-cancelled">❌ Visszavonva</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('Y-m-d H:i', strtotime($purchase['purchased_at'])); ?></td>
                            <td><?php echo $purchase['delivered_at'] ? date('Y-m-d H:i', strtotime($purchase['delivered_at'])) : '-'; ?></td>
                            <td>
                                <?php if ($purchase['status'] === 'pending'): ?>
                                    <form action="purchase_deliver.php" method="POST" style="display: inline;">
                                        <input type="hidden" name="purchase_id" value="<?php echo $purchase['id']; ?>">
                                        <button type="submit" class="btn-small btn-deliver" onclick="return confirm('Biztos kézbesítetted az itemet?')">
                                            ✅ Kézbesítve
                                        </button>
                                    </form>
                                    <form action="purchase_cancel.php" method="POST" style="display: inline;">
                                        <input type="hidden" name="purchase_id" value="<?php echo $purchase['id']; ?>">
                                        <button type="submit" class="btn-small btn-cancel" onclick="return confirm('Biztos visszavonod?')">
                                            ❌ Visszavonás
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span style="color: #9ca3af;">Nincs művelet</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 2rem;">Nincs vásárlás</td>
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
