<?php 
$pageTitle = 'L2 Savior - Ultimate Lineage 2 Server';

// Legutóbbi 3 hír lekérése
require_once 'php/db.php';
try {
    $pdoGS = getDBConnection(DB_GS);
    $stmtNews = $pdoGS->query("SELECT * FROM news WHERE published = 1 ORDER BY created_at DESC LIMIT 3");
    $latestNews = $stmtNews->fetchAll();
} catch (PDOException $e) {
    $latestNews = [];
}

include 'includes/header.php'; 
?>

<!-- Hero Section -->
<div class="hero">
    <h1>WELCOME TO L2 SAVIOR</h1>
    <p>Experience the Ultimate Lineage 2 Adventure</p>
    <div class="hero-buttons">
        <?php if ($isLoggedIn): ?>
            <a href="account.php" class="btn btn-primary">👤 My Account</a>
            <a href="shop.php" class="btn btn-secondary">🛒 Visit Shop</a>
        <?php else: ?>
            <a href="register.php" class="btn btn-primary">👤 Create Account</a>
            <a href="download.php" class="btn btn-secondary">⬇️ Download Client</a>
        <?php endif; ?>
    </div>
</div>

<!-- Latest News Section -->
<?php if (!empty($latestNews)): ?>
<section class="container" style="margin-top: 3rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2 style="font-size: 2rem; background: linear-gradient(90deg, #ffd700, #50c878); -webkit-background-clip: text; -webkit-text-fill-color: transparent; margin: 0; font-weight: 800;">
            📰 Latest News
        </h2>
        <a href="news.php" style="color: #ffd700; text-decoration: none; font-weight: 600; transition: color 0.3s;" onmouseover="this.style.color='#50c878'" onmouseout="this.style.color='#ffd700'">
            View All News →
        </a>
    </div>
    
    <div class="cards-grid">
        <?php foreach ($latestNews as $news): ?>
            <div class="card" style="display: flex; flex-direction: column;">
                <div style="margin-bottom: 0.5rem;">
                    <span style="color: #9ca3af; font-size: 0.85rem;">
                        📅 <?php echo date('Y-m-d', strtotime($news['created_at'])); ?>
                    </span>
                </div>
                
                <h3 style="color: #ffd700; margin-bottom: 1rem; line-height: 1.3;">
                    <?php echo htmlspecialchars($news['title']); ?>
                </h3>
                
                <p style="color: #b8b8c8; margin-bottom: 1rem; flex: 1; line-height: 1.6;">
                    <?php 
                    $content = htmlspecialchars($news['content']);
                    // Első 150 karakter
                    if (strlen($content) > 150) {
                        echo substr($content, 0, 150) . '...';
                    } else {
                        echo $content;
                    }
                    ?>
                </p>
                
                <?php if (!empty($news['tags'])): ?>
                    <div style="margin-bottom: 1rem;">
                        <?php 
                        $tags = array_slice(explode(',', $news['tags']), 0, 2); // Max 2 tag
                        foreach ($tags as $tag): 
                        ?>
                            <span style="display: inline-block; background: rgba(80, 200, 120, 0.2); color: #50c878; padding: 0.25rem 0.6rem; border-radius: 12px; font-size: 0.75rem; margin-right: 0.3rem;">
                                <?php echo htmlspecialchars(trim($tag)); ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <a href="news_view.php?id=<?php echo $news['id']; ?>" style="color: #ffd700; text-decoration: none; font-weight: 600; transition: color 0.3s;" onmouseover="this.style.color='#50c878'" onmouseout="this.style.color='#ffd700'">
                    Read More →
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- Features Cards -->
<section class="container" style="margin-top: 3rem;">
    <h2 style="text-align: center; font-size: 2rem; background: linear-gradient(90deg, #ffd700, #50c878); -webkit-background-clip: text; -webkit-text-fill-color: transparent; margin-bottom: 2rem; font-weight: 800;">
        Why Choose Us?
    </h2>
    <div class="cards-grid">
        <div class="card">
            <h3>⚔️ Balanced Gameplay</h3>
            <p>Carefully tuned rates and balanced PvP system for the best gaming experience.</p>
        </div>
        <div class="card">
            <h3>🛡️ Stable Server</h3>
            <p>99.9% uptime with dedicated hardware and daily backups.</p>
        </div>
        <div class="card">
            <h3>👥 Active Community</h3>
            <p>Join thousands of players and our dedicated Discord support.</p>
        </div>
        <div class="card">
            <h3>💎 Premium Shop</h3>
            <p>Fair donate system with cosmetics and quality of life items.</p>
        </div>
        <div class="card">
            <h3>🏰 Epic Raids</h3>
            <p>Challenge epic bosses with your clan and earn legendary rewards.</p>
        </div>
        <div class="card">
            <h3>🎮 Custom Events</h3>
            <p>Daily automated events including TvT, CTF, and Siege Wars.</p>
        </div>
    </div>
</section>

<!-- Server Info -->
<section class="container" style="margin-top: 3rem;">
    <div style="background: rgba(21, 21, 35, 0.8); border: 1px solid rgba(255, 215, 0, 0.2); border-radius: 12px; padding: 2rem;">
        <h2 style="color: #ffd700; text-align: center; margin-bottom: 1.5rem; font-weight: 800;">Server Information</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; color: #b8b8c8; text-align: center;">
            <div>
                <div style="font-size: 2rem; color: #50c878; font-weight: 900;">x5</div>
                <div>EXP / SP</div>
            </div>
            <div>
                <div style="font-size: 2rem; color: #50c878; font-weight: 900;">x3</div>
                <div>Adena</div>
            </div>
            <div>
                <div style="font-size: 2rem; color: #50c878; font-weight: 900;">x2</div>
                <div>Drop Rate</div>
            </div>
            <div>
                <div style="font-size: 2rem; color: #50c878; font-weight: 900;">Interlude</div>
                <div>Chronicle</div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
