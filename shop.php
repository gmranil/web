<?php
require_once 'php/db.php';

$pageTitle = 'Donate Shop - L2 Savior';

// Bejelentkezés ellenőrzés
$isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$username = $_SESSION['username'] ?? '';
$donateCoins = 0;

if ($isLoggedIn) {
    try {
        $pdoLS = getDBConnection(DB_LS);
        $stmt = $pdoLS->prepare("SELECT donate_coins FROM accounts WHERE login = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        $donateCoins = $user['donate_coins'] ?? 0;
    } catch (PDOException $e) {
        // Handle error
    }
}

// Kategória szűrő
$category = $_GET['category'] ?? 'all';

// Shop itemek lekérése
try {
    $pdoGS = getDBConnection(DB_GS);
    
    if ($category === 'all') {
        $stmt = $pdoGS->query("SELECT * FROM shop_items WHERE available = 1 ORDER BY category, price");
    } else {
        $stmt = $pdoGS->prepare("SELECT * FROM shop_items WHERE available = 1 AND category = ? ORDER BY price");
        $stmt->execute([$category]);
    }
    $shopItems = $stmt->fetchAll();
    
    // Kategóriák lekérése
    $stmtCat = $pdoGS->query("SELECT DISTINCT category FROM shop_items WHERE available = 1 ORDER BY category");
    $categories = $stmtCat->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $shopItems = [];
    $categories = [];
}

// Success/Error üzenetek
$success = $_SESSION['success'] ?? null;
$error = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);

include 'includes/header.php';
?>

<style>
    .shop-container { max-width: 1400px; margin: 2rem auto; padding: 0 2rem; }
    .shop-header {
        text-align: center;
        margin-bottom: 2rem;
    }
    .shop-header h1 {
        background: linear-gradient(90deg, #ffd700, #50c878);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        font-size: 2.5rem;
        font-weight: 800;
        margin-bottom: 0.5rem;
    }
    .coin-balance {
        display: inline-block;
        background: rgba(255, 215, 0, 0.1);
        border: 2px solid rgba(255, 215, 0, 0.3);
        padding: 0.8rem 1.5rem;
        border-radius: 8px;
        margin-top: 1rem;
    }
    .coin-balance .amount {
        font-size: 1.5rem;
        color: #ffd700;
        font-weight: 900;
    }
    .filter-tabs {
        display: flex;
        justify-content: center;
        gap: 1rem;
        margin-bottom: 2rem;
        flex-wrap: wrap;
    }
    .filter-tab {
        padding: 0.8rem 1.5rem;
        background: rgba(21, 21, 35, 0.6);
        border: 1px solid rgba(255, 215, 0, 0.2);
        border-radius: 8px;
        color: #b8b8c8;
        text-decoration: none;
        transition: all 0.3s;
        font-weight: 600;
    }
    .filter-tab:hover, .filter-tab.active {
        background: rgba(255, 215, 0, 0.2);
        border-color: #ffd700;
        color: #ffd700;
    }
    .shop-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1.5rem;
        margin-bottom: 3rem;
    }
    .shop-item {
        background: rgba(21, 21, 35, 0.8);
        border: 1px solid rgba(255, 215, 0, 0.2);
        border-radius: 12px;
        padding: 1.5rem;
        transition: all 0.3s;
    }
    .shop-item:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(255, 215, 0, 0.2);
        border-color: #ffd700;
    }
    .shop-item h3 {
        color: #ffd700;
        margin-bottom: 0.5rem;
        font-size: 1.1rem;
    }
    .shop-item .description {
        color: #b8b8c8;
        font-size: 0.9rem;
        margin-bottom: 1rem;
        line-height: 1.5;
        min-height: 60px;
    }
    .shop-item .price {
        font-size: 1.5rem;
        color: #50c878;
        font-weight: 900;
        margin-bottom: 1rem;
    }
    .shop-item .details {
        color: #9ca3af;
        font-size: 0.85rem;
        margin-bottom: 1rem;
    }
    .guest-notice {
        background: rgba(59, 130, 246, 0.1);
        border: 1px solid rgba(59, 130, 246, 0.3);
        padding: 2rem;
        border-radius: 12px;
        text-align: center;
        margin-bottom: 2rem;
    }
</style>

<div class="shop-container">
    <div class="shop-header">
        <h1>🛒 Donate Shop</h1>
        <p style="color: #b8b8c8; margin-bottom: 1rem;">Támogasd a szervert és szerezz exkluzív itemeket!</p>
        
        <?php if ($isLoggedIn): ?>
            <div class="coin-balance">
                <span style="color: #b8b8c8; font-size: 0.9rem;">Egyenleged: </span>
                <span class="amount"><?php echo number_format($donateCoins); ?> DC</span>
            </div>
        <?php endif; ?>
    </div>

    <!-- Success/Error Messages -->
    <?php if ($success): ?>
        <div style="background: rgba(34, 197, 94, 0.2); border: 1px solid rgba(34, 197, 94, 0.5); color: #86efac; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; text-align: center;">
            ✅ <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div style="background: rgba(220, 38, 38, 0.2); border: 1px solid rgba(220, 38, 38, 0.5); color: #fca5a5; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; text-align: center;">
            ⚠️ <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <!-- Guest Notice -->
    <?php if (!$isLoggedIn): ?>
        <div class="guest-notice">
            <h3 style="color: #3b82f6; margin-bottom: 1rem;">🔐 Bejelentkezés szükséges</h3>
            <p style="color: #b8b8c8; margin-bottom: 1.5rem;">A shop használatához be kell jelentkezned vagy regisztrálnod kell.</p>
            <a href="login.php" class="btn btn-primary" style="margin-right: 1rem;">Bejelentkezés</a>
            <a href="register.php" class="btn btn-secondary">Regisztráció</a>
        </div>
    <?php endif; ?>

    <!-- Category Filter -->
    <?php if (!empty($categories)): ?>
        <div class="filter-tabs">
            <a href="shop.php?category=all" class="filter-tab <?php echo $category === 'all' ? 'active' : ''; ?>">
                🌐 Összes
            </a>
            <?php foreach ($categories as $cat): ?>
                <a href="shop.php?category=<?php echo urlencode($cat); ?>" class="filter-tab <?php echo $category === $cat ? 'active' : ''; ?>">
                    <?php echo htmlspecialchars($cat); ?>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Shop Items -->
    <?php if (!empty($shopItems)): ?>
        <div class="shop-grid">
            <?php foreach ($shopItems as $item): ?>
                <div class="shop-item">
                    <h3><?php echo htmlspecialchars($item['item_name']); ?></h3>
                    
                    <?php if ($item['description']): ?>
                        <div class="description"><?php echo htmlspecialchars($item['description']); ?></div>
                    <?php endif; ?>
                    
                    <div class="details">
                        <?php if ($item['enchant'] > 0): ?>
                            ✨ Enchant: <strong style="color: #50c878;">+<?php echo $item['enchant']; ?></strong><br>
                        <?php endif; ?>
                        <?php if ($item['quantity'] > 1): ?>
                            📦 Mennyiség: <strong><?php echo $item['quantity']; ?></strong><br>
                        <?php endif; ?>
                        🏷️ Kategória: <strong><?php echo htmlspecialchars($item['category']); ?></strong>
                    </div>
                    
                    <div class="price"><?php echo number_format($item['price']); ?> DC</div>
                    
                    <?php if ($isLoggedIn): ?>
                        <form action="shop_buy.php" method="POST" onsubmit="return confirm('Biztos megveszed: <?php echo htmlspecialchars($item['item_name']); ?>?\nÁr: <?php echo $item['price']; ?> DC')">
                            <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                            <button type="submit" class="btn btn-primary" style="width: 100%;">
                                🛒 Vásárlás
                            </button>
                        </form>
                    <?php else: ?>
                        <button class="btn btn-secondary" style="width: 100%;" disabled>
                            🔒 Bejelentkezés szükséges
                        </button>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div style="text-align: center; padding: 3rem; background: rgba(21, 21, 35, 0.6); border-radius: 12px; color: #9ca3af;">
            Jelenleg nincsenek elérhető itemek ebben a kategóriában.
        </div>
    <?php endif; ?>

    <!-- Info -->
    <div style="background: rgba(251, 191, 36, 0.1); border: 1px solid rgba(251, 191, 36, 0.3); padding: 1.5rem; border-radius: 8px; margin-top: 2rem; color: #b8b8c8; text-align: center;">
        <strong style="color: #fbbf24;">💡 Fontos:</strong> A megvásárolt itemek a következő bejelentkezéskor automatikusan megérkeznek a karakteredhez!
    </div>
</div>

<?php include 'includes/footer.php'; ?>
