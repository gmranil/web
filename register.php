<?php
require_once 'php/csrf.php';

// Ha már be van jelentkezve, redirect
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: account.php');
    exit;
}

$pageTitle = 'Register - L2 Savior';
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
    .form-group input.success {
        border-color: #50c878;
    }
    .error-message {
        color: #fca5a5;
        font-size: 0.85rem;
        margin-top: 0.3rem;
    }
    .success-message {
        color: #86efac;
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
    .help-text {
        color: #9ca3af;
        font-size: 0.85rem;
        margin-top: 0.3rem;
    }
    .btn {
        width: 100%;
        font-size: 1.1rem;
        padding: 0.9rem;
        cursor: pointer;
    }
</style>

<div class="form-container">
    <h2>📝 Register</h2>
    
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
    
    <form action="php/register_process.php" method="POST" id="registerForm">
        <?php echo csrfField(); ?>
        
        <div class="form-group">
            <label for="username">Username *</label>
            <input type="text" id="username" name="username" required minlength="4" maxlength="16" 
                   placeholder="Choose a unique username" autofocus>
            <div class="help-text">4-16 characters, alphanumeric only</div>
        </div>
        
        <div class="form-group">
            <label for="password">Password *</label>
            <input type="password" id="password" name="password" required minlength="4" 
                   placeholder="Choose a strong password">
            <div class="help-text">Minimum 4 characters</div>
        </div>
        
        <div class="form-group">
            <label for="password_confirm">Confirm Password *</label>
            <input type="password" id="password_confirm" name="password_confirm" required 
                   placeholder="Re-enter your password">
        </div>
        
        <button type="submit" class="btn btn-primary">
            Create Account
        </button>
    </form>
    
    <div class="form-footer">
        <p>Already have an account? <a href="login.php">Login here</a></p>
        <p style="margin-top: 0.5rem;">
            <a href="index.php" style="font-size: 0.9rem;">← Back to Home</a>
        </p>
    </div>
</div>

<script>
// Real-time form validation
const form = document.getElementById('registerForm');
const username = document.getElementById('username');
const password = document.getElementById('password');
const passwordConfirm = document.getElementById('password_confirm');

// Username validation
username.addEventListener('blur', function() {
    const value = this.value.trim();
    clearFieldError(this);
    
    if (value.length === 0) {
        return; // Don't validate empty on blur
    }
    
    if (value.length < 4) {
        showFieldError(this, 'Username must be at least 4 characters');
    } else if (value.length > 16) {
        showFieldError(this, 'Username must be maximum 16 characters');
    } else if (!/^[a-zA-Z0-9]+$/.test(value)) {
        showFieldError(this, 'Username can only contain letters and numbers');
    } else {
        showFieldSuccess(this, '✓ Valid username');
    }
});

// Password validation
password.addEventListener('blur', function() {
    const value = this.value;
    clearFieldError(this);
    
    if (value.length === 0) {
        return;
    }
    
    if (value.length < 4) {
        showFieldError(this, 'Password must be at least 4 characters');
    } else {
        showFieldSuccess(this, '✓ Valid password');
    }
    
    // Re-validate password confirmation if it has value
    if (passwordConfirm.value.length > 0) {
        passwordConfirm.dispatchEvent(new Event('blur'));
    }
});

// Password confirmation validation
passwordConfirm.addEventListener('blur', function() {
    const value = this.value;
    clearFieldError(this);
    
    if (value.length === 0) {
        return;
    }
    
    if (value !== password.value) {
        showFieldError(this, 'Passwords do not match');
    } else {
        showFieldSuccess(this, '✓ Passwords match');
    }
});

// Form submit validation
form.addEventListener('submit', function(e) {
    // Clear all previous errors
    document.querySelectorAll('.error-message, .success-message').forEach(el => el.remove());
    document.querySelectorAll('input').forEach(el => {
        el.classList.remove('error');
        el.classList.remove('success');
    });
    
    let hasError = false;
    
    const usernameValue = username.value.trim();
    const passwordValue = password.value;
    const passwordConfirmValue = passwordConfirm.value;
    
    if (usernameValue.length < 4 || usernameValue.length > 16) {
        showFieldError(username, 'Username must be 4-16 characters');
        hasError = true;
    }
    
    if (!/^[a-zA-Z0-9]+$/.test(usernameValue)) {
        showFieldError(username, 'Username can only contain letters and numbers');
        hasError = true;
    }
    
    if (passwordValue.length < 4) {
        showFieldError(password, 'Password must be at least 4 characters');
        hasError = true;
    }
    
    if (passwordValue !== passwordConfirmValue) {
        showFieldError(passwordConfirm, 'Passwords do not match');
        hasError = true;
    }
    
    if (hasError) {
        e.preventDefault();
    }
});

function showFieldError(field, message) {
    field.classList.add('error');
    const error = document.createElement('div');
    error.className = 'error-message';
    error.textContent = message;
    field.parentElement.appendChild(error);
}

function showFieldSuccess(field, message) {
    field.classList.add('success');
    const success = document.createElement('div');
    success.className = 'success-message';
    success.textContent = message;
    field.parentElement.appendChild(success);
}

function clearFieldError(field) {
    field.classList.remove('error');
    field.classList.remove('success');
    const parent = field.parentElement;
    const error = parent.querySelector('.error-message');
    const success = parent.querySelector('.success-message');
    if (error) error.remove();
    if (success) success.remove();
}
</script>

<?php include 'includes/footer.php'; ?>

