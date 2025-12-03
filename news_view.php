<?php
require_once 'php/db.php';

// News ID lekérése
$newsId = $_GET['id'] ?? null;

if (!$newsId) {
    header('Location: news.php');
    exit;
}

// Hír lekérése
try {
    $pdo = getDBConnection(DB_GS);
    $stmt = $pdo->prepare("SELECT * FROM news WHERE id = ? AND published = 1");
    $stmt->execute([$newsId]);
    $news = $stmt->fetch();
    
    if (!$news) {
        header('Location: news.php');
        exit;
    }
} catch (PDOException $e) {
    header('Location: news.php');
    exit;
}

$pageTitle = htmlspecialchars($news['title']) . ' - L2 Savior';
include 'includes/header.php';
?>

<style>
    .news-view-container { max-width: 900px; margin: 2rem auto; padding: 0 2rem; }
    .news-article {
        background: rgba(21, 21, 35, 0.8);
        border: 1px solid rgba(255, 215, 0, 0.2);
        border-radius: 12px;
        padding: 2.5rem;
    }
    .news-article h1 {
        color: #ffd700;
        font-size: 2rem;
        margin-bottom: 1rem;
        line-height: 1.3;
    }
    .news-meta {
        color: #9ca3af;
        font-size: 0.9rem;
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid rgba(255, 215, 0, 0.1);
        display: flex;
        gap: 1.5rem;
        flex-wrap: wrap;
    }
    .news-meta span {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
    }
    .news-content {
        color: #b8b8c8;
        line-height: 1.9;
        font-size: 1.05rem;
    }
    .news-content p {
        margin-bottom: 1rem;
    }
    .news-tags {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
        margin-top: 2rem;
        padding-top: 1.5rem;
        border-top: 1px solid rgba(255, 215, 0, 0.1);
    }
    .news-tag {
        background: rgba(80, 200, 120, 0.2);
        color: #50c878;
        padding: 0.4rem 1rem;
        border-radius: 20px;
        font-size: 0.85rem;
        border: 1px solid rgba(80, 200, 120, 0.3);
    }
    .back-button {
        display: inline-block;
        margin-bottom: 2rem;
        color: #ffd700;
        text-decoration: none;
        font-weight: 600;
        transition: color 0.3s;
    }
    .back-button:hover {
        color: #50c878;
    }
</style>

<div class="news-view-container">
    <a href="news.php" class="back-button">← Vissza a hírekhez</a>
    
    <article class="news-article">
        <h1><?php echo htmlspecialchars($news['title']); ?></h1>
        
        <div class="news-meta">
            <span>📅 <?php echo date('Y-m-d H:i', strtotime($news['created_at'])); ?></span>
            <span>👤 <?php echo htmlspecialchars($news['author'] ?? 'Admin'); ?></span>
            <?php if ($news['updated_at'] && $news['updated_at'] != $news['created_at']): ?>
                <span>✏️ Frissítve: <?php echo date('Y-m-d H:i', strtotime($news['updated_at'])); ?></span>
            <?php endif; ?>
        </div>
        
        <div class="news-content">
            <?php echo nl2br(htmlspecialchars($news['content'])); ?>
        </div>
        
        <?php if (!empty($news['tags'])): ?>
            <div class="news-tags">
                <span style="color: #9ca3af; margin-right: 0.5rem;">🏷️ Címkék:</span>
                <?php 
                $tags = explode(',', $news['tags']);
                foreach ($tags as $tag): 
                ?>
                    <span class="news-tag"><?php echo htmlspecialchars(trim($tag)); ?></span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </article>
    
    <!-- Share / Actions -->
    <div style="margin-top: 2rem; text-align: center;">
        <a href="news.php" class="btn btn-secondary">← Vissza az összes hírhez</a>
    </div>
    
    <!-- Call to Action -->
    <div style="margin-top: 2rem; padding: 2rem; background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.3); border-radius: 8px; text-align: center;">
        <h3 style="color: #3b82f6; margin-bottom: 1rem;">💬 Csatlakozz közösségünkhöz!</h3>
        <p style="color: #b8b8c8; margin-bottom: 1.5rem;">
            Beszélgess más játékosokkal, oszd meg véleményed a hírekről és maradj naprakész!
        </p>
        <a href="https://discord.gg/yourdiscord" target="_blank" class="btn btn-primary">
            Discord Szerver
        </a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
