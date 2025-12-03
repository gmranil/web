<?php
require_once '../php/admin_auth.php';
require_once '../php/db.php';
require_once '../php/activity_logger.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: purchases.php');
    exit;
}

$purchaseId = $_POST['purchase_id'] ?? null;

if (!$purchaseId) {
    $_SESSION['error'] = "Érvénytelen vásárlás ID!";
    header('Location: purchases.php');
    exit;
}

try {
    $pdoLS = getDBConnection(DB_LS);
    $pdoGS = getDBConnection(DB_GS);
    
    // Purchase lekérése
    $stmt = $pdoGS->prepare("SELECT * FROM shop_purchases WHERE id = ?");
    $stmt->execute([$purchaseId]);
    $purchase = $stmt->fetch();
    
    if (!$purchase) {
        $_SESSION['error'] = "Vásárlás nem található!";
        header('Location: purchases.php');
        exit;
    }
    
    if ($purchase['status'] !== 'pending') {
        $_SESSION['error'] = "Ez a vásárlás már nincs függőben!";
        header('Location: purchases.php');
        exit;
    }
    
    // Tranzakció
    $pdoLS->beginTransaction();
    $pdoGS->beginTransaction();
    
    try {
        // Coin visszatérítés
        $stmtRefund = $pdoLS->prepare("
            UPDATE accounts 
            SET donate_coins = donate_coins + ? 
            WHERE login = ?
        ");
        $stmtRefund->execute([$purchase['price'], $purchase['username']]);
        
        // Új egyenleg lekérése
        $stmtBalance = $pdoLS->prepare("SELECT donate_coins FROM accounts WHERE login = ?");
        $stmtBalance->execute([$purchase['username']]);
        $newBalance = $stmtBalance->fetch()['donate_coins'];
        
        // Coin transaction log
        logCoinTransaction(
            $purchase['username'],
            $purchase['price'], // Pozitív (visszatérítés)
            $newBalance,
            'refund',
            "Refund: {$purchase['item_name']}"
        );
        
        // Purchase státusz frissítés
        $stmtCancel = $pdoGS->prepare("
            UPDATE shop_purchases 
            SET status = 'cancelled' 
            WHERE id = ?
        ");
        $stmtCancel->execute([$purchaseId]);
        
        // Commit
        $pdoLS->commit();
        $pdoGS->commit();
        
        $_SESSION['success'] = "Vásárlás visszavonva és {$purchase['price']} DC visszatérítve! User: {$purchase['username']}";
        
    } catch (Exception $e) {
        $pdoLS->rollBack();
        $pdoGS->rollBack();
        throw $e;
    }
    
} catch (PDOException $e) {
    $_SESSION['error'] = "Hiba történt: " . $e->getMessage();
    error_log("Purchase cancel error: " . $e->getMessage());
}

header('Location: purchases.php');
exit;
?>
