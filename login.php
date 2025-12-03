<?php
require_once 'php/csrf.php';

// Ha már be van jelentkezve, redirect
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: account.php');
    exit;
}

$pageTitle = 'Login - L2 Savior';
include 'includes/header.php';

// Error/Success üzenetek
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
    .form-group input.error {
        border-color: #dc2626;
    }
    .error-message {
        color: #fca5a5;
        font-size: 0.85rem;
        margin-top: 0.3rem;
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
    .form-footer {
        text-align: center;
        margin-top: 1.5rem;
        padding-top: 1.5rem;
        border-top: 1px solid rgba(255, 215, 0, 0.1);
        color: #b8b8c8;
    }
    .form-footer a {
        color: #ffd700;
        text-decoration: none;
        font-weight: 600;
        transition: color 0.3s;
    }
    .form-footer a:hover {
        color: #50c878;
    }
    .btn {
        width: 100%;
        font-size: 1.1rem;
        padding: 0.9rem;
        cursor: pointer;
    }
</style>

<div class="form-container">
    <h2>🔐 Login</h2>
    
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
    
    <form action="php/login_process.php" method="POST" id="loginForm">
        <?php echo csrfField(); ?>
        
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required minlength="3" maxlength="16" 
                   placeholder="Enter your username" autofocus>
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required minlength="4" 
                   placeholder="Enter your password">
        </div>
        
        <button type="submit" class="btn btn-primary">
            Login
        </button>
    </form>
    
    <div class="form-footer">
        <p>Don't have an account? <a href="register.php">Register here</a></p>
        <p style="margin-top: 0.5rem;">
            <a href="index.php" style="font-size: 0.9rem;">← Back to Home</a>
        </p>
    </div>
</div>

<script>
// Real-time form validation
document.getElementById('loginForm').addEventListener('submit', function(e) {
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value;
    
    // Clear previous errors
    document.querySelectorAll('.error-message').forEach(el => el.remove());
    document.querySelectorAll('input').forEach(el => el.classList.remove('error'));
    
    let hasError = false;
    
    if (username.length < 3) {
        showError('username', 'Username must be at least 3 characters');
        hasError = true;
    }
    
    if (password.length < 4) {
        showError('password', 'Password must be at least 4 characters');
        hasError = true;
    }
    
    if (hasError) {
        e.preventDefault();
    }
});

function showError(fieldId, message) {
    const field = document.getElementById(fieldId);
    field.classList.add('error');
    const error = document.createElement('div');
    error.className = 'error-message';
    error.textContent = message;
    field.parentElement.appendChild(error);
}
</script>

<?php include 'includes/footer.php'; ?>
