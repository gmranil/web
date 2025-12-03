<?php
require_once 'php/db.php';
require_once 'php/csrf.php';

// Check if logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$pageTitle = 'Change Password - L2 Savior';
include 'includes/header.php';

$error = $_SESSION['error'] ?? null;
$success = $_SESSION['success'] ?? null;
unset($_SESSION['error'], $_SESSION['success']);
?>

<style>
    .form-container {
        max-width: 450px;
        margin: 4rem auto;
        padding: 2.5rem;
        background: rgba(21, 21, 35, 0.9);
        border: 1px solid rgba(255, 215, 0, 0.2);
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
    }
    .form-container h2 {
        background: linear-gradient(90deg, #ffd700, #50c878);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        text-align: center;
        margin-bottom: 2rem;
        font-size: 2rem;
        font-weight: 800;
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
        padding: 0.9rem;
        background: rgba(15, 15, 26, 0.8);
        border: 1px solid rgba(255, 215, 0, 0.3);
        border-radius: 6px;
        color: #b8b8c8;
        font-size: 1rem;
        transition: all 0.3s;
        box-sizing: border-box;
    }
    .form-group input:focus {
        outline: none;
        border-color: #ffd700;
        box-shadow: 0 0 10px rgba(255, 215, 0, 0.2);
    }
    .alert-error {
        background: rgba(220, 38, 38, 0.2);
        border: 1px solid rgba(220, 38, 38, 0.5);
        color: #fca5a5;
        padding: 1rem;
        border-radius: 6px;
        margin-bottom: 1.5rem;
        text-align: center;
    }
    .alert-success {
        background: rgba(34, 197, 94, 0.2);
        border: 1px solid rgba(34, 197, 94, 0.5);
        color: #86efac;
        padding: 1rem;
        border-radius: 6px;
        margin-bottom: 1.5rem;
        text-align: center;
    }
    .btn {
        width: 100%;
        font-size: 1.1rem;
        padding: 0.9rem;
        cursor: pointer;
    }
    .back-link {
        text-align: center;
        margin-top: 1rem;
    }
    .back-link a {
        color: #ffd700;
        text-decoration: none;
        transition: color 0.3s;
    }
    .back-link a:hover {
        color: #50c878;
    }
</style>

<div class="form-container">
    <h2>🔑 Change Password</h2>
    
    <?php if ($error): ?>
        <div class="alert-error">
            ⚠️ <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert-success">
            ✅ <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>
    
    <form action="php/change_password_process.php" method="POST">
        <?php echo csrfField(); ?>
        
        <div class="form-group">
            <label for="old_password">Current Password</label>
            <input type="password" id="old_password" name="old_password" required minlength="4" autofocus>
        </div>
        
        <div class="form-group">
            <label for="new_password">New Password</label>
            <input type="password" id="new_password" name="new_password" required minlength="4">
        </div>
        
        <div class="form-group">
            <label for="confirm_password">Confirm New Password</label>
            <input type="password" id="confirm_password" name="confirm_password" required minlength="4">
        </div>
        
        <button type="submit" class="btn btn-primary">Change Password</button>
    </form>
    
    <div class="back-link">
        <a href="account.php">← Back to Account</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
