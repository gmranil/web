<?php
require_once '../php/admin_auth.php';
require_once '../php/db.php';

$newsId = $_GET['id'] ?? null;
$isEdit = !empty($newsId);

// Hír betöltése szerkesztéshez
if ($isEdit) {
    try {
        $pdo = getDBConnection(DB_GS);
        $stmt = $pdo->prepare("SELECT * FROM news WHERE id = ?");
        $stmt->execute([$newsId]);
        $news = $stmt->fetch();
        
        if (!$news) {
            $_SESSION['error'] = "Hír nem található!";
            header('Location: news.php');
            exit;
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Hiba: " . $e->getMessage();
        header('Location: news.php');
        exit;
    }
}

// Form feldolgozás
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $tags = trim($_POST['tags'] ?? '');
    $published = isset($_POST['published']) ? 1 : 0;
    
    if (empty($title) || empty($content)) {
        $error = "A cím és tartalom kötelező!";
    } else {
        try {
            $pdo = getDBConnection(DB_GS);
            
            if ($isEdit) {
                // Update
                $stmt = $pdo->prepare("
                    UPDATE news 
                    SET title = ?, content = ?, tags = ?, published = ?, updated_at = NOW() 
                    WHERE id = ?
                ");
                $stmt->execute([$title, $content, $tags, $published, $newsId]);
                $_SESSION['success'] = "Hír sikeresen frissítve!";
            } else {
                // Insert
                $stmt = $pdo->prepare("
                    INSERT INTO news (title, content, author, tags, published) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([$title, $content, ADMIN_USERNAME, $tags, $published]);
                $_SESSION['success'] = "Hír sikeresen létrehozva!";
            }
            
            header('Location: news.php');
            exit;
            
        } catch (PDOException $e) {
            $error = "Hiba: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isEdit ? 'Hír szerkesztése' : 'Új hír'; ?> - Admin Panel</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .admin-container { max-width: 900px; margin: 2rem auto; padding: 0 2rem; }
        .form-section { background: rgba(21, 21, 35, 0.8); border: 1px solid rgba(255, 215, 0, 0.2); border-radius: 12px; padding: 2rem; }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; color: #ffd700; margin-bottom: 0.5rem; font-weight: 600; }
        .form-group input[type="text"],
        .form-group textarea { width: 100%; padding: 0.8rem; background: rgba(15, 15, 26, 0.8); border: 1px solid rgba(255, 215, 0, 0.3); border-radius: 6px; color: #b8b8c8; font-size: 1rem; font-family: inherit; }
        .form-group textarea { min-height: 300px; resize: vertical; }
        .form-group input:focus,
        .form-group textarea:focus { outline: none; border-color: #ffd700; box-shadow: 0 0 10px rgba(255, 215, 0, 0.2); }
        .checkbox-group { display: flex; align-items: center; gap: 0.5rem; }
        .checkbox-group input[type="checkbox"] { width: auto; }
        .form-actions { display: flex; gap: 1rem; justify-content: flex-end; margin-top: 2rem; }
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
                <li><a href="logout.php">🚪 Kilépés</a></li>
            </ul>
        </div>
    </nav>

    <div class="admin-container">
        <h1 style="background: linear-gradient(90deg, #ffd700, #50c878); -webkit-background-clip: text; -webkit-text-fill-color: transparent; font-size: 2rem; margin-bottom: 2rem; font-weight: 800;">
            <?php echo $isEdit ? '✏️ Hír szerkesztése' : '➕ Új hír létrehozása'; ?>
        </h1>

        <?php if (isset($error)): ?>
            <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" class="form-section">
            <div class="form-group">
                <label for="title">Cím *</label>
                <input type="text" id="title" name="title" required 
                       value="<?php echo htmlspecialchars($news['title'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="content">Tartalom *</label>
                <textarea id="content" name="content" required><?php echo htmlspecialchars($news['content'] ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <label for="tags">Tag-ek (vesszővel elválasztva)</label>
                <input type="text" id="tags" name="tags" 
                       placeholder="pl: Esemény,PvP,Fontos"
                       value="<?php echo htmlspecialchars($news['tags'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <div class="checkbox-group">
                    <input type="checkbox" id="published" name="published" 
                           <?php echo ($news['published'] ?? 1) ? 'checked' : ''; ?>>
                    <label for="published" style="margin: 0; color: #b8b8c8;">Publikálva (ha nincs bejelölve, piszkozat marad)</label>
                </div>
            </div>

            <div class="form-actions">
                <a href="news.php" class="btn btn-secondary">❌ Mégse</a>
                <button type="submit" class="btn btn-primary">
                    <?php echo $isEdit ? '💾 Mentés' : '➕ Létrehozás'; ?>
                </button>
            </div>
        </form>
    </div>

    <footer>
        <p>&copy; 2025 L2 Savior - Admin Panel</p>
    </footer>
</body>
</html>
