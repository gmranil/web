<?php
require_once '../php/admin_auth.php';
require_once '../php/db.php';

$newsId = $_GET['id'] ?? null;

if (!$newsId) {
    $_SESSION['error'] = "Érvénytelen hír ID!";
    header('Location: news.php');
    exit;
}

try {
    $pdo = getDBConnection(DB_GS);
    $stmt = $pdo->prepare("DELETE FROM news WHERE id = ?");
    $stmt->execute([$newsId]);
    
    $_SESSION['success'] = "Hír sikeresen törölve!";
} catch (PDOException $e) {
    $_SESSION['error'] = "Hiba történt: " . $e->getMessage();
}

header('Location: news.php');
exit;
?>
