<?php
require_once 'php/db.php';

// Bejelentkezés ellenőrzés
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.html');
    exit;
}

$username = $_SESSION['username'];
$error = null;
$success = null;

// Jelenlegi email lekérése
try {
    $pdo = getDBConnection(DB_LS);
    $stmt = $pdo->prepare("SELECT email FROM accounts WHERE login = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    $currentEmail = $user['email'] ?? '';
} catch (PDOException $e) {
    $error = "Hiba az email lekérésekor.";
}

// Form feldolgozás
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newEmail = trim($_POST['new_email'] ?? '');
    $confirmEmail = trim($_POST['confirm_email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    // Validálás
    if (empty($newEmail) || empty($confirmEmail) || empty($password)) {
        $error = "Minden mező kitöltése kötelező!";
    } elseif (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
        $error = "Érvénytelen email formátum!";
    } elseif ($newEmail !== $confirmEmail) {
        $error = "Az email címek nem egyeznek!";
    } elseif ($newEmail === $currentEmail) {
        $error = "Az új email nem lehet ugyanaz, mint a jelenlegi!";
    } else {
        try {
            $pdo = getDBConnection(DB_LS);
            
            // Jelszó ellenőrzés
            $stmt = $pdo->prepare("SELECT password FROM accounts WHERE login = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            $passwordHash = base64_encode(pack('H*', sha1($password)));
            
            if ($user['password'] !== $passwordHash) {
                $error = "Hibás jelszó!";
            } else {
                // Email frissítés
                $stmtUpdate = $pdo->prepare("UPDATE accounts SET email = ? WHERE login = ?");
                $stmtUpdate->execute([$newEmail, $username]);
                
                // Activity log
                require_once 'php/activity_logger.php';
                logActivity($username, 'email_change', "Email changed to: " . substr($newEmail, 0, 3) . "***");
                
                $success = "Email cím sikeresen megváltoztatva!";
                $currentEmail = $newEmail;
            }
            
        } catch (PDOException $e) {
            $error = "Hiba történt: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email módosítás - L2 Savior</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .form-container {
            max-width: 500px;
            margin: 4rem auto;
            padding: 3rem;
            background: rgba(21, 21, 35, 0.8);
            border: 1px solid rgba(255, 215, 0, 0.2);
            border-radius: 12px;
        }
        .form-container h2 {
            background: linear-gradient(90deg, #ffd700, #50c878);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-align: center;
            margin-bottom: 2rem;
            font-size: 2rem;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            color: #ffd700;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        .form-group input {
            width: 100%;
            padding: 0.8rem;
            background: rgba(15, 15, 26, 0.8);
            border: 1px solid rgba(255, 215, 0, 0.3);
            border-radius: 6px;
            color: #b8b8c8;
            font-size: 1rem;
        }
        .form-group input:focus {
            outline: none;
            border-color: #ffd700;
            box-shadow: 0 0 10px rgba(255, 215, 0, 0.2);
        }
        .error {
            background: rgba(220, 38, 38, 0.2);
            border: 1px solid rgba(220, 38, 38, 0.5);
            color: #fca5a5;
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1rem;
            text-align: center;
        }
        .success {
            background: rgba(34, 197, 94, 0.2);
            border: 1px solid rgba(34, 197, 94, 0.5);
            color: #86efac;
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1rem;
            text-align: center;
        }
        .info-box {
            background: rgba(80, 200, 120, 0.1);
            border: 1px solid rgba(80, 200, 120, 0.3);
            color: #b8b8c8;
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }
        .current-email {
            background: rgba(255, 215, 0, 0.1);
            border: 1px solid rgba(255, 215, 0, 0.2);
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        .current-email .label {
            color: #9ca3af;
            font-size: 0.85rem;
            margin-bottom: 0.3rem;
        }
        .current-email .value {
            color: #ffd700;
            font-weight: 600;
            font-size: 1.1rem;
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 1.5rem;
            color: #50c878;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <nav>
        <div class="container">
            <div class="logo">L2 SAVIOR</div>
            <ul>
                <li><a href="index.html">Home</a></li>
                <li><a href="account.php">Fiókom</a></li>
                <li><a href="shop.php">Shop</a></li>
                <li><a href="logout.php">Kilépés</a></li>
            </ul>
        </div>
    </nav>

    <div class="form-container">
        <h2>✉️ Email módosítás</h2>
        
        <div class="current-email">
            <div class="label">Jelenlegi email cím:</div>
            <div class="value"><?php echo $currentEmail ? htmlspecialchars($currentEmail) : 'Nincs beállítva'; ?></div>
        </div>

        <div class="info-box">
            ℹ️ Az email cím módosításához meg kell adnod a jelszavadat biztonsági okokból.
        </div>

        <?php if ($error): ?>
            <div class="error">❌ <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success">✅ <?php echo htmlspecialchars($success); ?></div>
        <?php else: ?>
            <form method="POST">
                <div class="form-group">
                    <label for="new_email">Új email cím *</label>
                    <input type="email" id="new_email" name="new_email" required 
                           placeholder="ujmail@example.com">
                </div>

                <div class="form-group">
                    <label for="confirm_email">Email megerősítése *</label>
                    <input type="email" id="confirm_email" name="confirm_email" required 
                           placeholder="ujmail@example.com">
                </div>

                <div class="form-group">
                    <label for="password">Jelszavad *</label>
                    <input type="password" id="password" name="password" required 
                           placeholder="Biztonsági ellenőrzés">
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    💾 Email módosítása
                </button>
            </form>
        <?php endif; ?>
        
        <a href="account.php" class="back-link">← Vissza a fiókomhoz</a>
    </div>

    <footer>
        <p>&copy; 2025 L2 Savior - All Rights Reserved</p>
    </footer>
</body>
</html>
