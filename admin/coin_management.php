<?php
require_once '../php/admin_auth.php';
require_once '../php/activity_logger.php';

$success = $_SESSION['success'] ?? null;
$error = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);

// Form feldolgozás
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $operation = $_POST['operation'] ?? '';
    $usernames = trim($_POST['usernames'] ?? '');
    $amount = intval($_POST['amount'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    
    if (empty($usernames) || $amount <= 0) {
        $error = "Felhasználónevek és összeg megadása kötelező!";
    } else {
        // Username-ek feldolgozása (soronként vagy vesszővel elválasztva)
        $usernameList = preg_split('/[\n,]+/', $usernames);
        $usernameList = array_filter(array_map('trim', $usernameList));
        
        if (empty($usernameList)) {
            $error = "Érvénytelen felhasználónevek!";
        } else {
            try {
                $pdo = getDBConnection(DB_LS);
                $successCount = 0;
                $failedUsers = [];
                
                foreach ($usernameList as $username) {
                    try {
                        // User ellenőrzés
                        $stmtCheck = $pdo->prepare("SELECT donate_coins FROM accounts WHERE login = ?");
                        $stmtCheck->execute([$username]);
                        $user = $stmtCheck->fetch();
                        
                        if (!$user) {
                            $failedUsers[] = "$username (nem létezik)";
                            continue;
                        }
                        
                        // Coin művelet
                        if ($operation === 'add') {
                            $newBalance = $user['donate_coins'] + $amount;
                            $stmtUpdate = $pdo->prepare("UPDATE accounts SET donate_coins = ? WHERE login = ?");
                            $stmtUpdate->execute([$newBalance, $username]);
                            
                            // Log
                            logCoinTransaction($username, $amount, $newBalance, 'admin_add', $description ?: "Admin bulk add by " . ADMIN_USERNAME);
                            logActivity($username, 'coin_add', "Admin added $amount DC");
                            
                        } elseif ($operation === 'remove') {
                            if ($user['donate_coins'] < $amount) {
                                $failedUsers[] = "$username (nincs elég coin: {$user['donate_coins']} DC)";
                                continue;
                            }
                            
                            $newBalance = $user['donate_coins'] - $amount;
                            $stmtUpdate = $pdo->prepare("UPDATE accounts SET donate_coins = ? WHERE login = ?");
                            $stmtUpdate->execute([$newBalance, $username]);
                            
                            // Log
                            logCoinTransaction($username, -$amount, $newBalance, 'admin_remove', $description ?: "Admin bulk remove by " . ADMIN_USERNAME);
                            
                        } elseif ($operation === 'set') {
                            $stmtUpdate = $pdo->prepare("UPDATE accounts SET donate_coins = ? WHERE login = ?");
                            $stmtUpdate->execute([$amount, $username]);
                            
                            // Log
                            $diff = $amount - $user['donate_coins'];
                            logCoinTransaction($username, $diff, $amount, 'admin_add', $description ?: "Admin set balance to $amount by " . ADMIN_USERNAME);
                        }
                        
                        $successCount++;
                        
                    } catch (PDOException $e) {
                        $failedUsers[] = "$username (hiba: " . $e->getMessage() . ")";
                    }
                }
                
                // Eredmény üzenet
                $resultMsg = "Sikeres műveletek: $successCount";
                if (!empty($failedUsers)) {
                    $resultMsg .= " | Sikertelen: " . count($failedUsers) . " (" . implode(', ', array_slice($failedUsers, 0, 5));
                    if (count($failedUsers) > 5) {
                        $resultMsg .= " és még " . (count($failedUsers) - 5) . " user";
                    }
                    $resultMsg .= ")";
                }
                
                if ($successCount > 0) {
                    $_SESSION['success'] = $resultMsg;
                } else {
                    $_SESSION['error'] = $resultMsg;
                }
                
                header('Location: coin_management.php');
                exit;
                
            } catch (PDOException $e) {
                $error = "Hiba történt: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coin Management - Admin Panel</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .admin-container { max-width: 1000px; margin: 2rem auto; padding: 0 2rem; }
        .form-section { background: rgba(21, 21, 35, 0.8); border: 1px solid rgba(255, 215, 0, 0.2); border-radius: 12px; padding: 2rem; margin-bottom: 2rem; }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; color: #ffd700; margin-bottom: 0.5rem; font-weight: 600; }
        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group textarea,
        .form-group select { width: 100%; padding: 0.8rem; background: rgba(15, 15, 26, 0.8); border: 1px solid rgba(255, 215, 0, 0.3); border-radius: 6px; color: #b8b8c8; font-size: 1rem; font-family: inherit; }
        .form-group textarea { min-height: 120px; resize: vertical; font-family: monospace; }
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus { outline: none; border-color: #ffd700; box-shadow: 0 0 10px rgba(255, 215, 0, 0.2); }
        .radio-group { display: flex; gap: 2rem; }
        .radio-option { display: flex; align-items: center; gap: 0.5rem; }
        .radio-option input[type="radio"] { width: auto; }
        .radio-option label { margin: 0; color: #b8b8c8; cursor: pointer; }
        .help-text { color: #9ca3af; font-size: 0.85rem; margin-top: 0.3rem; }
        .alert-success { background: rgba(34, 197, 94, 0.2); border: 1px solid rgba(34, 197, 94, 0.5); color: #86efac; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; }
        .alert-error { background: rgba(220, 38, 38, 0.2); border: 1px solid rgba(220, 38, 38, 0.5); color: #fca5a5; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; }
        .info-box { background: rgba(80, 200, 120, 0.1); border: 1px solid rgba(80, 200, 120, 0.3); color: #b8b8c8; padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem; }
        .info-box h3 { color: #50c878; margin-top: 0; margin-bottom: 1rem; }
        .info-box ul { margin: 0; padding-left: 1.5rem; }
        .info-box li { margin-bottom: 0.5rem; }
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
    <li><a href="coin_management.php">💰 Coin Management</a></li>
    <li><a href="logout.php">🚪 Kilépés</a></li>
	</ul>
        </div>
    </nav>

    <div class="admin-container">
        <h1 style="background: linear-gradient(90deg, #ffd700, #50c878); -webkit-background-clip: text; -webkit-text-fill-color: transparent; font-size: 2rem; margin-bottom: 2rem; font-weight: 800;">
            💰 Donate Coin Management
        </h1>

        <?php if ($success): ?>
            <div class="alert-success">✅ <?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert-error">⚠️ <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="info-box">
            <h3>ℹ️ Használati útmutató</h3>
            <ul>
                <li><strong>Hozzáadás (Add):</strong> A megadott összeg hozzáadódik a jelenlegi egyenleghez</li>
                <li><strong>Levonás (Remove):</strong> A megadott összeg levonódik (ha van elég coin)</li>
                <li><strong>Beállítás (Set):</strong> Az egyenleg a megadott összegre lesz beállítva</li>
                <li><strong>Több user:</strong> Soronként vagy vesszővel elválasztva add meg a username-eket</li>
                <li><strong>Log:</strong> Minden művelet automatikusan naplózódik</li>
            </ul>
        </div>

        <form method="POST" class="form-section">
            <div class="form-group">
                <label>Művelet típusa *</label>
                <div class="radio-group">
                    <div class="radio-option">
                        <input type="radio" id="add" name="operation" value="add" checked>
                        <label for="add">➕ Hozzáadás</label>
                    </div>
                    <div class="radio-option">
                        <input type="radio" id="remove" name="operation" value="remove">
                        <label for="remove">➖ Levonás</label>
                    </div>
                    <div class="radio-option">
                        <input type="radio" id="set" name="operation" value="set">
                        <label for="set">⚙️ Beállítás</label>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="usernames">Felhasználónevek *</label>
                <textarea id="usernames" name="usernames" required 
                          placeholder="Pl:&#10;player1&#10;player2&#10;player3&#10;&#10;vagy: player1, player2, player3"></textarea>
                <p class="help-text">Soronként vagy vesszővel elválasztva</p>
            </div>

            <div class="form-group">
                <label for="amount">Összeg (DC) *</label>
                <input type="number" id="amount" name="amount" required min="1" 
                       placeholder="pl: 1000">
            </div>

            <div class="form-group">
                <label for="description">Megjegyzés (opcionális)</label>
                <input type="text" id="description" name="description" 
                       placeholder="pl: Event díj, Kompenzáció, stb.">
                <p class="help-text">Ez megjelenik a coin transaction log-ban</p>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;" 
                    onclick="return confirm('Biztos végrehajtod ezt a műveletet?\n\nEllenőrizd a felhasználóneveket és az összeget!')">
                💾 Művelet végrehajtása
            </button>
        </form>

        <!-- Quick Actions -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div style="background: rgba(21, 21, 35, 0.6); border: 1px solid rgba(255, 215, 0, 0.15); border-radius: 8px; padding: 1.5rem;">
                <h3 style="color: #ffd700; margin-top: 0;">📊 Legutóbbi coin műveletek</h3>
                <?php
                try {
                    $pdoLS = getDBConnection(DB_LS);
                    $stmtRecent = $pdoLS->query("
                        SELECT * FROM coin_transactions 
                        WHERE type IN ('admin_add', 'admin_remove') 
                        ORDER BY created_at DESC 
                        LIMIT 5
                    ");
                    $recentTx = $stmtRecent->fetchAll();
                    
                    if (!empty($recentTx)) {
                        echo '<ul style="list-style: none; padding: 0; margin: 0; color: #b8b8c8; font-size: 0.9rem;">';
                        foreach ($recentTx as $tx) {
                            $color = $tx['amount'] > 0 ? '#50c878' : '#dc2626';
                            echo '<li style="padding: 0.5rem 0; border-bottom: 1px solid rgba(255,215,0,0.05);">';
                            echo '<strong>' . htmlspecialchars($tx['username']) . '</strong>: ';
                            echo '<span style="color: ' . $color . '; font-weight: 600;">' . ($tx['amount'] > 0 ? '+' : '') . number_format($tx['amount']) . ' DC</span><br>';
                            echo '<small style="color: #9ca3af;">' . date('Y-m-d H:i', strtotime($tx['created_at'])) . '</small>';
                            echo '</li>';
                        }
                        echo '</ul>';
                    } else {
                        echo '<p style="color: #9ca3af; text-align: center;">Nincs adat</p>';
                    }
                } catch (PDOException $e) {
                    echo '<p style="color: #dc2626;">Hiba: ' . htmlspecialchars($e->getMessage()) . '</p>';
                }
                ?>
            </div>

            <div style="background: rgba(21, 21, 35, 0.6); border: 1px solid rgba(255, 215, 0, 0.15); border-radius: 8px; padding: 1.5rem;">
                <h3 style="color: #ffd700; margin-top: 0;">💎 Top 5 Coin tulajdonos</h3>
                <?php
                try {
                    $pdoLS = getDBConnection(DB_LS);
                    $stmtTop = $pdoLS->query("
                        SELECT login, donate_coins 
                        FROM accounts 
                        WHERE donate_coins > 0 
                        ORDER BY donate_coins DESC 
                        LIMIT 5
                    ");
                    $topUsers = $stmtTop->fetchAll();
                    
                    if (!empty($topUsers)) {
                        echo '<ul style="list-style: none; padding: 0; margin: 0; color: #b8b8c8; font-size: 0.9rem;">';
                        $position = 1;
                        foreach ($topUsers as $user) {
                            $medal = $position === 1 ? '🥇' : ($position === 2 ? '🥈' : ($position === 3 ? '🥉' : ''));
                            echo '<li style="padding: 0.5rem 0; border-bottom: 1px solid rgba(255,215,0,0.05);">';
                            echo $medal . ' <strong>' . htmlspecialchars($user['login']) . '</strong>: ';
                            echo '<span style="color: #ffd700; font-weight: 600;">' . number_format($user['donate_coins']) . ' DC</span>';
                            echo '</li>';
                            $position++;
                        }
                        echo '</ul>';
                    } else {
                        echo '<p style="color: #9ca3af; text-align: center;">Nincs adat</p>';
                    }
                } catch (PDOException $e) {
                    echo '<p style="color: #dc2626;">Hiba: ' . htmlspecialchars($e->getMessage()) . '</p>';
                }
                ?>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; 2025 L2 Savior - Admin Panel</p>
    </footer>
</body>
</html>
