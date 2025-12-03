<?php
require_once '../php/admin_auth.php';
require_once '../php/db.php';

$itemId = $_GET['id'] ?? null;
$isEdit = !empty($itemId);

// Item betöltése szerkesztéshez
if ($isEdit) {
    try {
        $pdo = getDBConnection(DB_GS);
        $stmt = $pdo->prepare("SELECT * FROM shop_items WHERE id = ?");
        $stmt->execute([$itemId]);
        $item = $stmt->fetch();
        
        if (!$item) {
            $_SESSION['error'] = "Item nem található!";
            header('Location: shop_items.php');
            exit;
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Hiba: " . $e->getMessage();
        header('Location: shop_items.php');
        exit;
    }
}

// Form feldolgozás
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $itemName = trim($_POST['item_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $itemIdL2 = intval($_POST['item_id'] ?? 0);
    $enchant = intval($_POST['enchant'] ?? 0);
    $quantity = intval($_POST['quantity'] ?? 1);
    $price = intval($_POST['price'] ?? 0);
    $category = trim($_POST['category'] ?? '');
    $available = isset($_POST['available']) ? 1 : 0;
    
    if (empty($itemName) || empty($category) || $price <= 0) {
        $error = "Item név, kategória és ár (>0) kötelező!";
    } else {
        try {
            $pdo = getDBConnection(DB_GS);
            
            if ($isEdit) {
                // Update
                $stmt = $pdo->prepare("
                    UPDATE shop_items 
                    SET item_name = ?, description = ?, item_id = ?, enchant = ?, 
                        quantity = ?, price = ?, category = ?, available = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $itemName, $description, $itemIdL2, $enchant,
                    $quantity, $price, $category, $available, $itemId
                ]);
                $_SESSION['success'] = "Item sikeresen frissítve!";
            } else {
                // Insert
                $stmt = $pdo->prepare("
                    INSERT INTO shop_items (item_name, description, item_id, enchant, quantity, price, category, available) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $itemName, $description, $itemIdL2, $enchant,
                    $quantity, $price, $category, $available
                ]);
                $_SESSION['success'] = "Item sikeresen létrehozva!";
            }
            
            header('Location: shop_items.php');
            exit;
            
        } catch (PDOException $e) {
            $error = "Hiba: " . $e->getMessage();
        }
    }
}

// Kategóriák lekérése
try {
    $pdo = getDBConnection(DB_GS);
    $stmtCat = $pdo->query("SELECT DISTINCT category FROM shop_items ORDER BY category");
    $categories = $stmtCat->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $categories = ['Enchant', 'Jewelry', 'Premium', 'Service', 'Books'];
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isEdit ? 'Item szerkesztése' : 'Új item'; ?> - Admin Panel</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .admin-container { max-width: 900px; margin: 2rem auto; padding: 0 2rem; }
        .form-section { background: rgba(21, 21, 35, 0.8); border: 1px solid rgba(255, 215, 0, 0.2); border-radius: 12px; padding: 2rem; }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; color: #ffd700; margin-bottom: 0.5rem; font-weight: 600; }
        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group textarea,
        .form-group select { width: 100%; padding: 0.8rem; background: rgba(15, 15, 26, 0.8); border: 1px solid rgba(255, 215, 0, 0.3); border-radius: 6px; color: #b8b8c8; font-size: 1rem; font-family: inherit; }
        .form-group textarea { min-height: 120px; resize: vertical; }
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus { outline: none; border-color: #ffd700; box-shadow: 0 0 10px rgba(255, 215, 0, 0.2); }
        .checkbox-group { display: flex; align-items: center; gap: 0.5rem; }
        .checkbox-group input[type="checkbox"] { width: auto; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        .form-actions { display: flex; gap: 1rem; justify-content: flex-end; margin-top: 2rem; }
        .alert-error { background: rgba(220, 38, 38, 0.2); border: 1px solid rgba(220, 38, 38, 0.5); color: #fca5a5; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; }
        .help-text { color: #9ca3af; font-size: 0.85rem; margin-top: 0.3rem; }
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
                <li><a href="logout.php">🚪 Kilépés</a></li>
            </ul>
        </div>
    </nav>

    <div class="admin-container">
        <h1 style="background: linear-gradient(90deg, #ffd700, #50c878); -webkit-background-clip: text; -webkit-text-fill-color: transparent; font-size: 2rem; margin-bottom: 2rem; font-weight: 800;">
            <?php echo $isEdit ? '✏️ Item szerkesztése' : '➕ Új item létrehozása'; ?>
        </h1>

        <?php if (isset($error)): ?>
            <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" class="form-section">
            <div class="form-group">
                <label for="item_name">Item név *</label>
                <input type="text" id="item_name" name="item_name" required 
                       value="<?php echo htmlspecialchars($item['item_name'] ?? ''); ?>"
                       placeholder="pl: Blessed Scroll: Enchant Weapon (S)">
            </div>

            <div class="form-group">
                <label for="description">Leírás</label>
                <textarea id="description" name="description" 
                          placeholder="Item részletes leírása..."><?php echo htmlspecialchars($item['description'] ?? ''); ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="item_id">L2 Item ID *</label>
                    <input type="number" id="item_id" name="item_id" required min="0"
                           value="<?php echo htmlspecialchars($item['item_id'] ?? '0'); ?>">
                    <p class="help-text">0 = szolgáltatás (name change, stb.)</p>
                </div>

                <div class="form-group">
                    <label for="price">Ár (DC) *</label>
                    <input type="number" id="price" name="price" required min="1"
                           value="<?php echo htmlspecialchars($item['price'] ?? ''); ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="enchant">Enchant Level</label>
                    <input type="number" id="enchant" name="enchant" min="0" max="30"
                           value="<?php echo htmlspecialchars($item['enchant'] ?? '0'); ?>">
                </div>

                <div class="form-group">
                    <label for="quantity">Mennyiség</label>
                    <input type="number" id="quantity" name="quantity" min="1"
                           value="<?php echo htmlspecialchars($item['quantity'] ?? '1'); ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="category">Kategória *</label>
                <select id="category" name="category" required>
                    <option value="">-- Válassz kategóriát --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat); ?>"
                                <?php echo (isset($item['category']) && $item['category'] === $cat) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat); ?>
                        </option>
                    <?php endforeach; ?>
                    <option value="__new__">+ Új kategória</option>
                </select>
                <input type="text" id="new_category" style="display:none; margin-top:0.5rem;" placeholder="Új kategória neve">
            </div>

            <div class="form-group">
                <div class="checkbox-group">
                    <input type="checkbox" id="available" name="available" 
                           <?php echo (isset($item['available']) && $item['available']) || !$isEdit ? 'checked' : ''; ?>>
                    <label for="available" style="margin: 0; color: #b8b8c8;">Elérhető a Shop-ban</label>
                </div>
            </div>

            <div class="form-actions">
                <a href="shop_items.php" class="btn btn-secondary">❌ Mégse</a>
                <button type="submit" class="btn btn-primary">
                    <?php echo $isEdit ? '💾 Mentés' : '➕ Létrehozás'; ?>
                </button>
            </div>
        </form>
    </div>

    <footer>
        <p>&copy; 2025 L2 Savior - Admin Panel</p>
    </footer>

    <script>
        // Új kategória kezelés
        document.getElementById('category').addEventListener('change', function() {
            const newCatInput = document.getElementById('new_category');
            if (this.value === '__new__') {
                newCatInput.style.display = 'block';
                newCatInput.required = true;
            } else {
                newCatInput.style.display = 'none';
                newCatInput.required = false;
            }
        });

        // Form submit - új kategória kezelés
        document.querySelector('form').addEventListener('submit', function(e) {
            const categorySelect = document.getElementById('category');
            const newCatInput = document.getElementById('new_category');
            
            if (categorySelect.value === '__new__' && newCatInput.value.trim()) {
                // Új kategória értékének beállítása
                const newOption = document.createElement('option');
                newOption.value = newCatInput.value.trim();
                newOption.selected = true;
                categorySelect.appendChild(newOption);
            }
        });
    </script>
</body>
</html>
