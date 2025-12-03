<?php
require_once '../php/admin_auth.php';
require_once '../php/db.php';

$itemId = $_GET['id'] ?? null;

if (!$itemId) {
    $_SESSION['error'] = "Érvénytelen item ID!";
    header('Location: shop_items.php');
    exit;
}

try {
    $pdo = getDBConnection(DB_GS);
    
    // Ellenőrizzük, hogy van-e pending purchase ezzel az itemmel
    $stmtCheck = $pdo->prepare("SELECT COUNT(*) as count FROM shop_purchases WHERE item_id = ? AND status = 'pending'");
    $stmtCheck->execute([$itemId]);
    $result = $stmtCheck->fetch();
    
    if ($result['count'] > 0) {
        $_SESSION['error'] = "Nem törölhető! Van {$result['count']} db függőben lévő vásárlás ezzel az itemmel.";
        header('Location: shop_items.php');
        exit;
    }
    
    // Törlés
    $stmt = $pdo->prepare("DELETE FROM shop_items WHERE id = ?");
    $stmt->execute([$itemId]);
    
    $_SESSION['success'] = "Item sikeresen törölve!";
} catch (PDOException $e) {
    $_SESSION['error'] = "Hiba történt: " . $e->getMessage();
}

header('Location: shop_items.php');
exit;
?>
