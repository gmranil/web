<?php
require_once '../php/admin_auth.php';
require_once '../php/db.php';

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
    $pdo = getDBConnection(DB_GS);
    
    // Purchase lekérése
    $stmt = $pdo->prepare("SELECT * FROM shop_purchases WHERE id = ?");
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
    
    // Státusz frissítése
    $stmtUpdate = $pdo->prepare("
        UPDATE shop_purchases 
        SET status = 'delivered', delivered_at = NOW() 
        WHERE id = ?
    ");
    $stmtUpdate->execute([$purchaseId]);
    
    $_SESSION['success'] = "Vásárlás sikeresen kézbesítve! User: {$purchase['username']}, Item: {$purchase['item_name']}";
    
} catch (PDOException $e) {
    $_SESSION['error'] = "Hiba történt: " . $e->getMessage();
}

header('Location: purchases.php');
exit;
?>
