<?php
require_once '../php/admin_auth.php';

// Hírek lekérése
try {
    $pdo = getDBConnection(DB_GS);
    $stmt = $pdo->query("SELECT * FROM news ORDER BY created_at DESC");
    $newsList = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Hiba: " . $e->getMessage();
}

// Sikeres/hiba üzenetek
$success = $_SESSION['success'] ?? null;
$errorMsg = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hírek kezelése - Admin Panel</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .admin-container { max-width: 1400px; margin: 2rem auto; padding: 0 2rem; }
        .admin-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        .admin-header h1 { background: linear-gradient(90deg, #ffd700, #50c878); -webkit-background-clip: text; -webkit-text-fill-color: transparent; font-size: 2rem; font-weight: 800; }
        .news-card { background: rgba(21, 21, 35, 0.8); border: 1px solid rgba(255, 215, 0, 0.2); border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem; }
        .news-card-header { display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem; }
        .news-card h3 { color: #ffd700; font-size: 1.3rem; margin-bottom: 0.5rem; }
        .news-meta { color: #b8b8c8; font-size: 0.85rem; }
        .news-content { color: #b8b8c8; line-height: 1.8; margin-bottom: 1rem; max-height: 100px; overflow: hidden; text-overflow: ellipsis; }
        .news-actions { display: flex; gap: 0.5rem; }
        .btn-edit { padding: 0.5rem 1rem; background: rgba(255, 215, 0, 0.2); color: #ffd700; border: 1px solid rgba(255, 215, 0, 0.3); border-radius: 6px; text-decoration: none; font-size: 0.9rem; }
        .btn-delete { padding: 0.5rem 1rem; background: rgba(220, 38, 38, 0.2); color: #dc2626; border: 1px solid rgba(220, 38, 38, 0.5); border-radius: 6px; text-decoration: none; font-size: 0.9rem; }
        .alert-success { background: rgba(34, 197, 94, 0.2); border: 1px solid rgba(34, 197, 94, 0.5); color: #86efac; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; }
        .alert-error { background: rgba(220, 38, 38, 0.2); border: 1px solid rgba(220, 38, 38, 0.5); color: #fca5a5; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; }
        .badge { display: inline-block; padding: 0.3rem 0.6rem; background: rgba(255, 215, 0, 0.2); color: #ffd700; border-radius: 4px; font-size: 0.8rem; margin-right: 0.3rem; }
        .badge-draft { background: rgba(107, 114, 128, 0.2); color: #9ca3af; }
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
            <h1>📰 Hírek kezelése</h1>
            <a href="news_edit.php" class="btn btn-primary">➕ Új hír</a>
        </div>

        <?php if ($success): ?>
            <div class="alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if ($errorMsg): ?>
            <div class="alert-error"><?php echo htmlspecialchars($errorMsg); ?></div>
        <?php endif; ?>

        <!-- News List -->
        <?php if (!empty($newsList)): ?>
            <?php foreach ($newsList as $news): ?>
                <div class="news-card">
                    <div class="news-card-header">
                        <div style="flex: 1;">
                            <h3><?php echo htmlspecialchars($news['title']); ?></h3>
                            <div class="news-meta">
                                Szerző: <?php echo htmlspecialchars($news['author']); ?> | 
                                Létrehozva: <?php echo date('Y-m-d H:i', strtotime($news['created_at'])); ?>
                                <?php if ($news['updated_at']): ?>
                                    | Módosítva: <?php echo date('Y-m-d H:i', strtotime($news['updated_at'])); ?>
                                <?php endif; ?>
                            </div>
                            <?php if ($news['tags']): ?>
                                <div style="margin-top: 0.5rem;">
                                    <?php foreach (explode(',', $news['tags']) as $tag): ?>
                                        <span class="badge"><?php echo htmlspecialchars(trim($tag)); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            <?php if (!$news['published']): ?>
                                <span class="badge badge-draft">🔒 Piszkozat</span>
                            <?php endif; ?>
                        </div>
                        <div class="news-actions">
                            <a href="news_edit.php?id=<?php echo $news['id']; ?>" class="btn-edit">✏️ Szerkesztés</a>
                            <a href="news_delete.php?id=<?php echo $news['id']; ?>" class="btn-delete" onclick="return confirm('Biztos törlöd ezt a hírt?')">🗑️ Törlés</a>
                        </div>
                    </div>
                    <div class="news-content">
                        <?php echo nl2br(htmlspecialchars(substr($news['content'], 0, 200))); ?>...
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="text-align: center; padding: 3rem; color: #b8b8c8;">
                Még nincs hír. Hozz létre egyet! 📝
            </div>
        <?php endif; ?>
    </div>

    <footer>
        <p>&copy; 2025 L2 Savior - Admin Panel</p>
    </footer>
</body>
</html>
