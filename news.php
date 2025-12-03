<?php
require_once 'php/db.php';

$pageTitle = 'News & Updates - L2 Savior';

// Hírek lekérése
try {
    $pdo = getDBConnection(DB_GS);
    $stmt = $pdo->query("SELECT * FROM news WHERE published = 1 ORDER BY created_at DESC");
    $newsList = $stmt->fetchAll();
} catch (PDOException $e) {
    $newsList = [];
}

include 'includes/header.php';
?>

<style>
    .news-container { max-width: 1000px; margin: 2rem auto; padding: 0 2rem; }
    .news-header {
        text-align: center;
        margin-bottom: 3rem;
    }
    .news-header h1 {
        background: linear-gradient(90deg, #ffd700, #50c878);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        font-size: 2.5rem;
        font-weight: 800;
        margin-bottom: 0.5rem;
    }
    .news-item {
        background: rgba(21, 21, 35, 0.8);
        border: 1px solid rgba(255, 215, 0, 0.2);
        border-radius: 12px;
        padding: 2rem;
        margin-bottom: 2rem;
        transition: all 0.3s;
    }
    .news-item:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 30px rgba(255, 215, 0, 0.15);
        border-color: rgba(255, 215, 0, 0.4);
    }
    .news-item h2 {
        color: #ffd700;
        margin-bottom: 0.5rem;
        font-size: 1.5rem;
    }
    .news-meta {
        color: #9ca3af;
        font-size: 0.9rem;
        margin-bottom: 1rem;
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }
    .news-meta span {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
    }
    .news-content {
        color: #b8b8c8;
        line-height: 1.8;
        margin-bottom: 1rem;
    }
    .news-tags {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
        margin-top: 1rem;
    }
    .news-tag {
        background: rgba(80, 200, 120, 0.2);
        color: #50c878;
        padding: 0.3rem 0.8rem;
        border-radius: 20px;
        font-size: 0.85rem;
    }
    .read-more {
        display: inline-block;
        color: #ffd700;
        text-decoration: none;
        font-weight: 600;
        margin-top: 0.5rem;
        transition: color 0.3s;
    }
    .read-more:hover {
        color: #50c878;
    }
    .empty-state {
        text-align: center;
        padding: 3rem;
        background: rgba(21, 21, 35, 0.6);
        border-radius: 12px;
        color: #9ca3af;
    }
</style>

<div class="news-container">
    <div class="news-header">
        <h1>📰 News & Updates</h1>
        <p style="color: #b8b8c8;">Legfrissebb hírek és események a szerverről</p>
    </div>

    <?php if (!empty($newsList)): ?>
        <?php foreach ($newsList as $news): ?>
            <article class="news-item">
                <h2><?php echo htmlspecialchars($news['title']); ?></h2>
                
                <div class="news-meta">
                    <span>📅 <?php echo date('Y-m-d', strtotime($news['created_at'])); ?></span>
                    <span>👤 <?php echo htmlspecialchars($news['author'] ?? 'Admin'); ?></span>
                </div>
                
                <div class="news-content">
                    <?php 
                    $content = htmlspecialchars($news['content']);
                    // Ha hosszabb mint 300 karakter, vágd le és add hozzá a "Tovább olvasom" linket
                    if (strlen($content) > 300) {
                        echo nl2br(substr($content, 0, 300)) . '...';
                        echo '<br><a href="news_view.php?id=' . $news['id'] . '" class="read-more">Tovább olvasom →</a>';
                    } else {
                        echo nl2br($content);
                    }
                    ?>
                </div>
                
                <?php if (!empty($news['tags'])): ?>
                    <div class="news-tags">
                        <?php 
                        $tags = explode(',', $news['tags']);
                        foreach ($tags as $tag): 
                        ?>
                            <span class="news-tag"><?php echo htmlspecialchars(trim($tag)); ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (strlen($news['content']) > 300): ?>
                    <a href="news_view.php?id=<?php echo $news['id']; ?>" class="btn btn-secondary" style="margin-top: 1rem;">
                        📖 Teljes cikk olvasása
                    </a>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="empty-state">
            <h3 style="color: #ffd700; margin-bottom: 1rem;">Nincs megjeleníthető hír</h3>
            <p>Hamarosan érkeznek az első hírek!</p>
        </div>
    <?php endif; ?>
    
    <div style="text-align: center; margin-top: 2rem; padding: 1.5rem; background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.3); border-radius: 8px;">
        <h3 style="color: #3b82f6; margin-bottom: 0.5rem;">📢 Kövess minket!</h3>
        <p style="color: #b8b8c8; margin-bottom: 1rem;">Ne maradj le a legfrissebb hírekről és eseményekről!</p>
        <a href="https://discord.gg/yourdiscord" target="_blank" class="btn btn-primary">💬 Csatlakozz Discord-ra</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
