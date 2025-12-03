<?php 
require_once 'php/db.php';

// Bejelentkezés ellenőrzés
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$pageTitle = 'Fiókom - L2 Savior';
$username = $_SESSION['username'];

// Donate Coins lekérése
try {
    $pdoLS = getDBConnection(DB_LS);
    $stmtCoins = $pdoLS->prepare("SELECT donate_coins, email FROM accounts WHERE login = ?");
    $stmtCoins->execute([$username]);
    $userAccount = $stmtCoins->fetch();
    $donateCoins = $userAccount['donate_coins'] ?? 0;
    $userEmail = $userAccount['email'] ?? '';
} catch (PDOException $e) {
    $donateCoins = 0;
    $userEmail = '';
}

// Karakterek lekérése
try {
    $pdoGS = getDBConnection(DB_GS);
    $stmt = $pdoGS->prepare("
        SELECT char_name, level, base_class, online 
        FROM characters 
        WHERE account_name = ? 
        ORDER BY level DESC
    ");
    $stmt->execute([$username]);
    $characters = $stmt->fetchAll();
} catch (PDOException $e) {
    $characters = [];
}

// Vásárlási előzmények
try {
    $pdoGS = getDBConnection(DB_GS);
    $stmtPurchases = $pdoGS->prepare("
        SELECT * FROM shop_purchases 
        WHERE username = ? 
        ORDER BY purchased_at DESC 
        LIMIT 10
    ");
    $stmtPurchases->execute([$username]);
    $purchases = $stmtPurchases->fetchAll();
} catch (PDOException $e) {
    $purchases = [];
}

// Coin Transactions
try {
    $pdoLS = getDBConnection(DB_LS);
    $stmtCoinTx = $pdoLS->prepare("
        SELECT * FROM coin_transactions 
        WHERE username = ? 
        ORDER BY created_at DESC 
        LIMIT 20
    ");
    $stmtCoinTx->execute([$username]);
    $coinTransactions = $stmtCoinTx->fetchAll();
} catch (PDOException $e) {
    $coinTransactions = [];
}

// Account Activity Log
try {
    $pdoLS = getDBConnection(DB_LS);
    $stmtActivity = $pdoLS->prepare("
        SELECT * FROM account_activity 
        WHERE username = ? 
        ORDER BY created_at DESC 
        LIMIT 15
    ");
    $stmtActivity->execute([$username]);
    $activities = $stmtActivity->fetchAll();
} catch (PDOException $e) {
    $activities = [];
}

// Success/Error üzenetek
$success = $_SESSION['success'] ?? null;
$error = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);

include 'includes/header.php'; 
?>

<style>
    .account-container { max-width: 1200px; margin: 2rem auto; padding: 0 2rem; }
    .account-header { 
        background: linear-gradient(135deg, rgba(21, 21, 35, 0.9), rgba(10, 14, 19, 0.9)); 
        border: 1px solid rgba(255, 215, 0, 0.2); 
        border-radius: 12px; 
        padding: 2rem; 
        margin-bottom: 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }
    .account-header h1 { 
        background: linear-gradient(90deg, #ffd700, #50c878); 
        -webkit-background-clip: text; 
        -webkit-text-fill-color: transparent; 
        font-size: 2rem; 
        font-weight: 800;
        margin: 0;
    }
    .coin-display {
        text-align: center;
        padding: 1rem 2rem;
        background: rgba(255, 215, 0, 0.1);
        border: 2px solid rgba(255, 215, 0, 0.3);
        border-radius: 8px;
    }
    .coin-display .amount {
        font-size: 2rem;
        background: linear-gradient(90deg, #ffd700, #ffed4e);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        font-weight: 900;
        display: block;
    }
    .coin-display .label { color: #b8b8c8; font-size: 0.85rem; }
    .section-title {
        background: linear-gradient(90deg, #ffd700, #50c878);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        font-size: 1.8rem;
        margin: 3rem 0 1.5rem 0;
        font-weight: 800;
    }
    .data-table {
        background: rgba(21, 21, 35, 0.6);
        border: 1px solid rgba(255, 215, 0, 0.15);
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 2rem;
    }
    .data-table table { width: 100%; border-collapse: collapse; }
    .data-table th {
        background: rgba(255, 215, 0, 0.1);
        color: #ffd700;
        padding: 1rem;
        text-align: left;
        font-weight: 600;
    }
    .data-table td {
        padding: 1rem;
        color: #b8b8c8;
        border-bottom: 1px solid rgba(255, 215, 0, 0.05);
    }
    .data-table tr:hover { background: rgba(255, 215, 0, 0.05); }
    .status-delivered { color: #50c878; }
    .status-pending { color: #fbbf24; }
    .status-cancelled { color: #dc2626; }
    .quick-actions {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }
</style>

<div class="account-container">
    <!-- Account Header -->
    <div class="account-header">
        <div>
            <h1>Üdv, <?php echo htmlspecialchars($username); ?>! 👋</h1>
            <p style="color: #b8b8c8; margin: 0.5rem 0 0 0;">Account vezérlőpult</p>
            <?php if ($userEmail): ?>
                <p style="color: #9ca3af; font-size: 0.9rem; margin-top: 0.3rem;">📧 <?php echo htmlspecialchars($userEmail); ?></p>
            <?php endif; ?>
        </div>
        <div class="coin-display">
            <span class="amount"><?php echo number_format($donateCoins); ?> DC</span>
            <span class="label">Donate Coin</span>
        </div>
    </div>

    <!-- Success/Error Messages -->
    <?php if ($success): ?>
        <div style="background: rgba(34, 197, 94, 0.2); border: 1px solid rgba(34, 197, 94, 0.5); color: #86efac; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
            ✅ <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div style="background: rgba(220, 38, 38, 0.2); border: 1px solid rgba(220, 38, 38, 0.5); color: #fca5a5; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
            ⚠️ <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <!-- Quick Actions -->
    <div class="quick-actions">
        <a href="shop.php" class="btn btn-primary" style="text-align: center;">🛒 Shop</a>
        <a href="download.php" class="btn btn-secondary" style="text-align: center;">⬇️ Kliens letöltés</a>
        <a href="change_password.php" class="btn btn-secondary" style="text-align: center;">🔑 Jelszó csere</a>
        <a href="change_email.php" class="btn btn-secondary" style="text-align: center;">✉️ Email módosítás</a>
    </div>

    <!-- Karakterek -->
    <h2 class="section-title">⚔️ Karaktereim</h2>
    <div class="cards-grid">
        <?php if (empty($characters)): ?>
            <div class="card">
                <p>Még nincs karaktered. Lépj be a játékba és hozz létre egyet!</p>
            </div>
        <?php else: ?>
            <?php foreach ($characters as $char): ?>
                <div class="card">
                    <h3><?php echo htmlspecialchars($char['char_name']); ?></h3>
                    <p>
                        <strong>Level:</strong> <?php echo $char['level']; ?><br>
                        <strong>Class ID:</strong> <?php echo $char['base_class']; ?><br>
                        <strong>Státusz:</strong> 
                        <?php if ($char['online']): ?>
                            <span style="color: #50c878;">🟢 Online</span>
                        <?php else: ?>
                            <span style="color: #9ca3af;">⚫ Offline</span>
                        <?php endif; ?>
                    </p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Vásárlási előzmények -->
    <?php if (!empty($purchases)): ?>
        <h2 class="section-title">🛒 Vásárlási előzmények</h2>
        <div class="data-table">
            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Ár</th>
                        <th>Státusz</th>
                        <th>Vásárlás dátuma</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($purchases as $purchase): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($purchase['item_name']); ?></strong></td>
                            <td style="color: #ffd700; font-weight: 600;"><?php echo number_format($purchase['price']); ?> DC</td>
                            <td>
                                <?php if ($purchase['status'] === 'delivered'): ?>
                                    <span class="status-delivered">✅ Kézbesítve</span>
                                <?php elseif ($purchase['status'] === 'pending'): ?>
                                    <span class="status-pending">⏳ Függőben</span>
                                <?php else: ?>
                                    <span class="status-cancelled">❌ Visszavonva</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('Y-m-d H:i', strtotime($purchase['purchased_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <!-- Coin Transaction History -->
    <?php if (!empty($coinTransactions)): ?>
        <h2 class="section-title">💰 Coin Tranzakciók</h2>
        <div class="data-table">
            <table>
                <thead>
                    <tr>
                        <th>Típus</th>
                        <th>Összeg</th>
                        <th>Egyenleg utána</th>
                        <th>Leírás</th>
                        <th>Dátum</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($coinTransactions as $tx): ?>
                        <tr>
                            <td>
                                <?php 
                                $typeLabels = [
                                    'purchase' => '🛒 Vásárlás',
                                    'donation' => '💳 Vásárlás',
                                    'admin_add' => '➕ Admin hozzáadás',
                                    'admin_remove' => '➖ Admin levonás',
                                    'refund' => '↩️ Visszatérítés'
                                ];
                                echo $typeLabels[$tx['type']] ?? $tx['type'];
                                ?>
                            </td>
                            <td style="<?php echo $tx['amount'] >= 0 ? 'color: #50c878;' : 'color: #dc2626;'; ?> font-weight: 600;">
                                <?php echo $tx['amount'] >= 0 ? '+' : ''; ?><?php echo number_format($tx['amount']); ?> DC
                            </td>
                            <td style="color: #ffd700;"><?php echo number_format($tx['balance_after']); ?> DC</td>
                            <td><?php echo htmlspecialchars($tx['description'] ?? '-'); ?></td>
                            <td><?php echo date('Y-m-d H:i', strtotime($tx['created_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <!-- Account Activity Log -->
    <?php if (!empty($activities)): ?>
        <h2 class="section-title">📋 Fiók Aktivitás</h2>
        <div class="data-table">
            <table>
                <thead>
                    <tr>
                        <th>Tevékenység</th>
                        <th>IP cím</th>
                        <th>Részletek</th>
                        <th>Dátum</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($activities as $activity): ?>
                        <tr>
                            <td>
                                <?php 
                                $activityLabels = [
                                    'login' => '🔓 Bejelentkezés',
                                    'logout' => '🔒 Kijelentkezés',
                                    'password_change' => '🔑 Jelszó csere',
                                    'email_change' => '✉️ Email csere',
                                    'purchase' => '🛒 Vásárlás',
                                    'coin_add' => '💰 Coin hozzáadás'
                                ];
                                echo $activityLabels[$activity['activity_type']] ?? $activity['activity_type'];
                                ?>
                            </td>
                            <td style="font-family: monospace; font-size: 0.9rem;">
                                <?php echo htmlspecialchars($activity['ip_address'] ?? '-'); ?>
                            </td>
                            <td style="font-size: 0.9rem; max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                <?php echo htmlspecialchars($activity['details'] ?? '-'); ?>
                            </td>
                            <td><?php echo date('Y-m-d H:i', strtotime($activity['created_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
