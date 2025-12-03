<?php
require_once 'php/config.php';
require_once 'php/db.php';
require_once 'php/activity_logger.php';

// Bejelentkezés ellenőrzés
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.html');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: shop.php');
    exit;
}

$username = $_SESSION['username'];
$itemId = $_POST['item_id'] ?? null;

if (!$itemId) {
    $_SESSION['error'] = "Érvénytelen item!";
    header('Location: shop.php');
    exit;
}

try {
    $pdoLS = getDBConnection(DB_LS);
    $pdoGS = getDBConnection(DB_GS);
    
    // Item lekérése
    $stmtItem = $pdoGS->prepare("SELECT * FROM shop_items WHERE id = ? AND available = 1");
    $stmtItem->execute([$itemId]);
    $item = $stmtItem->fetch();
    
    if (!$item) {
        $_SESSION['error'] = "Ez az item nem elérhető!";
        header('Location: shop.php');
        exit;
    }
    
    // User coin egyenleg lekérése
    $stmtUser = $pdoLS->prepare("SELECT donate_coins FROM accounts WHERE login = ?");
    $stmtUser->execute([$username]);
    $user = $stmtUser->fetch();
    $currentCoins = $user['donate_coins'] ?? 0;
    
    // Elég coin van?
    if ($currentCoins < $item['price']) {
        $_SESSION['error'] = "Nincs elég Donate Coin-od! Szükséges: {$item['price']} DC, Rendelkezésre áll: {$currentCoins} DC";
        header('Location: shop.php');
        exit;
    }
    
    // Tranzakció kezdése
    $pdoLS->beginTransaction();
    $pdoGS->beginTransaction();
    
    try {
        // Coin levonása
        $newBalance = $currentCoins - $item['price'];
        $stmtUpdate = $pdoLS->prepare("UPDATE accounts SET donate_coins = ? WHERE login = ?");
        $stmtUpdate->execute([$newBalance, $username]);
        
        // Coin transaction log
        logCoinTransaction(
            $username, 
            -$item['price'], // Negatív (levonás)
            $newBalance, 
            'purchase', 
            "Shop: {$item['item_name']}"
        );
        
        // Vásárlás rögzítése
        $stmtPurchase = $pdoGS->prepare("
            INSERT INTO shop_purchases (username, item_id, item_name, price, status) 
            VALUES (?, ?, ?, ?, 'pending')
        ");
        $stmtPurchase->execute([
            $username,
            $item['id'],
            $item['item_name'],
            $item['price']
        ]);
        
        // Activity log
        logActivity($username, 'purchase', "Purchased: {$item['item_name']} for {$item['price']} DC");
        
        // Commit mindkét adatbázisban
        $pdoLS->commit();
        $pdoGS->commit();
        
        $_SESSION['success'] = "Sikeres vásárlás! Item: {$item['item_name']} ({$item['price']} DC). Az item a következő bejelentkezéskor érkezik meg a karakteredhez! Új egyenleg: {$newBalance} DC";
        header('Location: shop.php');
        exit;
        
    } catch (Exception $e) {
        // Rollback mindkét adatbázisban
        $pdoLS->rollBack();
        $pdoGS->rollBack();
        throw $e;
    }
    
} catch (PDOException $e) {
    $_SESSION['error'] = "Hiba történt a vásárlás során. Kérlek próbáld újra később.";
    error_log("Shop purchase error for user $username: " . $e->getMessage());
    header('Location: shop.php');
    exit;
} catch (Exception $e) {
    $_SESSION['error'] = "Hiba történt a vásárlás során. Kérlek próbáld újra később.";
    error_log("Shop purchase error for user $username: " . $e->getMessage());
    header('Location: shop.php');
    exit;
}
?>
