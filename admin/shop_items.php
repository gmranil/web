<?php
require_once '../php/admin_auth.php';

// Shop itemek lekérése
try {
    $pdo = getDBConnection(DB_GS);
    $stmt = $pdo->query("SELECT * FROM shop_items ORDER BY category, price");
    $shopItems = $stmt->fetchAll();
} catch (PDOException $e) {
    $shopItems = [];
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
    <title>Shop Items - Admin Panel</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .admin-container { max-width: 1400px; margin: 2rem auto; padding: 0 2rem; }
        .admin-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem; }
        .admin-header h1 { background: linear-gradient(90deg, #ffd700, #50c878); -webkit-background-clip: text; -webkit-text-fill-color: transparent; font-size: 2rem; font-weight: 800; margin: 0; }
        .data-table { width: 100%; border-collapse: collapse; background: rgba(21, 21, 35, 0.6); border-radius: 12px; overflow: hidden; }
        .data-table th { background: rgba(255, 215, 0, 0.1); color: #ffd700; padding: 1rem; text-align: left; font-weight: 600; }
        .data-table td { padding: 1rem; color: #b8b8c8; border-bottom: 1px solid rgba(255, 215, 0, 0.05); }
        .data-table tr:hover { background: rgba(255, 215, 0, 0.05); }
        .data-table tr:last-child td { border-bottom: none; }
        .btn-small { padding: 0.4rem 0.8rem; border-radius: 4px; text-decoration: none; font-size: 0.85rem; margin-right: 0.5rem; display: inline-block; }
        .btn-edit { background: rgba(255, 215, 0, 0.2); color: #ffd700; border: 1px solid rgba(255, 215, 0, 0.3); }
        .btn-edit:hover { background: rgba(255, 215, 0, 0.3); }
        .btn-delete { background: rgba(220, 38, 38, 0.2); color: #dc2626; border: 1px solid rgba(220, 38, 38, 0.5); }
        .btn-delete:hover { background: rgba(220, 38, 38, 0.3); }
        .badge { display: inline-block; padding: 0.3rem 0.6rem; border-radius: 4px; font-size: 0.8rem; }
        .badge-available { background: rgba(80, 200, 120, 0.2); color: #50c878; }
        .badge-unavailable { background: rgba(107, 114, 128, 0.2); color: #9ca3af; }
        .alert-success { background: rgba(34, 197, 94, 0.2); border: 1px solid rgba(34, 197, 94, 0.5); color: #86efac; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; }
        .alert-error { background: rgba(220, 38, 38, 0.2); border: 1px solid rgba(220, 38, 38, 0.5); color: #fca5a5; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; }
        .category-badge { display: inline-block; background: rgba(80, 200, 120, 0.2); color: #50c878; padding: 0.3rem 0.6rem; border-radius: 4px; font-size: 0.8rem; margin-right: 0.5rem; }
        .item-description { max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 0.9rem; color: #9ca3af; }
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
            <h1>🛒 Shop Items kezelése</h1>
            <a href="shop_item_edit.php" class="btn btn-primary">➕ Új item</a>
        </div>

        <?php if ($success): ?>
            <div class="alert-success">✅ <?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if ($errorMsg): ?>
            <div class="alert-error">⚠️ <?php echo htmlspecialchars($errorMsg); ?></div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert-error">⚠️ <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 60px;">ID</th>
                    <th>Item név</th>
                    <th>Kategória</th>
                    <th style="width: 120px;">Ár (DC)</th>
                    <th style="width: 100px;">Item ID</th>
                    <th style="width: 80px;">Enchant</th>
                    <th style="width: 120px;">Státusz</th>
                    <th style="width: 200px;">Műveletek</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($shopItems)): ?>
                    <?php foreach ($shopItems as $item): ?>
                        <tr>
                            <td><strong>#<?php echo $item['id']; ?></strong></td>
                            <td>
                                <strong style="color: #ffd700;"><?php echo htmlspecialchars($item['item_name']); ?></strong>
                                <?php if ($item['description']): ?>
                                    <div class="item-description" title="<?php echo htmlspecialchars($item['description']); ?>">
                                        <?php echo htmlspecialchars($item['description']); ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="category-badge"><?php echo htmlspecialchars($item['category']); ?></span>
                            </td>
                            <td style="color: #ffd700; font-weight: 600;">
                                <?php echo number_format($item['price']); ?> DC
                            </td>
                            <td style="font-family: monospace; font-size: 0.9rem;">
                                <?php echo $item['item_id']; ?>
                            </td>
                            <td>
                                <?php if ($item['enchant'] > 0): ?>
                                    <span style="color: #50c878; font-weight: 600;">+<?php echo $item['enchant']; ?></span>
                                <?php else: ?>
                                    <span style="color: #6b7280;">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($item['available']): ?>
                                    <span class="badge badge-available">✅ Elérhető</span>
                                <?php else: ?>
                                    <span class="badge badge-unavailable">❌ Nem elérhető</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="shop_item_edit.php?id=<?php echo $item['id']; ?>" class="btn-small btn-edit">
                                    ✏️ Szerkesztés
                                </a>
                                <a href="shop_item_delete.php?id=<?php echo $item['id']; ?>" 
                                   class="btn-small btn-delete" 
                                   onclick="return confirm('Biztos törlöd ezt az itemet?\n\nItem: <?php echo htmlspecialchars($item['item_name']); ?>\nÁr: <?php echo $item['price']; ?> DC')">
                                    🗑️ Törlés
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 3rem; color: #9ca3af;">
                            <?php if (isset($error)): ?>
                                Hiba történt az adatok betöltésekor.
                            <?php else: ?>
                                Még nincs shop item. Hozz létre egyet! 
                                <br><br>
                                <a href="shop_item_edit.php" class="btn btn-primary">➕ Új item létrehozása</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <?php if (!empty($shopItems)): ?>
            <p style="color: #9ca3af; text-align: center; margin-top: 1.5rem; font-size: 0.9rem;">
                Összesen <?php echo count($shopItems); ?> item a shopban
            </p>
        <?php endif; ?>
    </div>

    <footer>
        <p>&copy; 2025 L2 Savior - Admin Panel</p>
    </footer>
</body>
</html>
