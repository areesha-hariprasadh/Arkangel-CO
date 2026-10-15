<?php
session_start();
require_once 'includes/db.php';

// Already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$errors = [];
$old    = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $old['email'] = htmlspecialchars(strip_tags(trim($_POST['email'] ?? '')));
    $password     = $_POST['password'] ?? '';
    $remember     = isset($_POST['remember']);

    // ── Validation ──────────────────────────────────────────
    if (empty($old['email'])) {
        $errors['email'] = 'Email address is required.';
    } elseif (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }

    if (empty($password)) {
        $errors['password'] = 'Password is required.';
    }

    // ── Check credentials ────────────────────────────────────
    if (empty($errors)) {
        $stmt = $conn->prepare(
            "SELECT id, first_name, password FROM users WHERE email = ? LIMIT 1"
        );
        $stmt->bind_param("s", $old['email']);
        $stmt->execute();
        $stmt->store_result();

        // ← ADD THESE THREE LINES RIGHT HERE
        $id         = null;
        $first_name = null;
        $hashed     = null;

        $stmt->bind_result($id, $first_name, $hashed);
        $stmt->fetch();

        if ($stmt->num_rows === 0) {
            // User doesn't exist — tell them to sign up
            $errors['email'] = 'No account found with this email. Please sign up.';
        } elseif (!password_verify($password, $hashed ?? '')) {
            $errors['password'] = 'Incorrect password. Please try again.';
        } else {
            // ── Success ──────────────────────────────────────
            $_SESSION['user_id']   = $id;
            $_SESSION['user_name'] = $first_name ?? '';   

            // Remember me — 30 day cookie
            if ($remember) {
                $token = bin2hex(random_bytes(32));
                $stmt2 = $conn->prepare(
                    "UPDATE users SET remember_token = ? WHERE id = ?"
                );
                $stmt2->bind_param("si", $token, $id);
                $stmt2->execute();
                $stmt2->close();
                setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', false, true);
            }

            header("Location: index.php");
            exit;
        }
        $stmt->close();
    }
}

// ── Auto-login via remember me cookie ───────────────────────
$currentPage = basename($_SERVER['PHP_SELF']);
if ($currentPage !== 'login.php' && !isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];
    $stmt  = $conn->prepare(
        "SELECT id, first_name FROM users WHERE remember_token = ? LIMIT 1"
    );
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->store_result();

    // ← ADD THESE TWO LINES RIGHT HERE
    $id         = null;
    $first_name = null;

    $stmt->bind_result($id, $first_name);
    $stmt->fetch();

    if ($stmt->num_rows > 0) {
        $_SESSION['user_id']   = $id;
        $_SESSION['user_name'] = $first_name ?? '';   // ← and change this line
        header("Location: index.php");
        exit;
    }
    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="style-global.css">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;1,300;1,400&family=Pinyon+Script&family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        /* ── Field wrap ── */
        .ark-login-field-wrap {
            margin-bottom: 20px;
        }

        /* ── Error states ── */
        .ark-login-input-wrapper.has-error input {
            border-color: #c0392b !important;
        }

        .ark-field-error {
            display: block;
            color: #9b1c1c;
            font-family: 'Montserrat', sans-serif;
            font-size: 0.68rem;
            font-weight: 500;
            letter-spacing: 0.04em;
            margin-top: 5px;
            margin-left: 2px;
        }
    </style>
</head>
<body class="login-page">

<div class="ark-login-container">

    <!-- LEFT PANEL -->
    <div class="ark-login-left-panel">
        <img src="images/login.png" alt="Luxury Jewelry">
        <div class="ark-login-overlay">
            <div class="ark-login-diamond-top">
                <i class="fa-regular fa-gem"></i>
            </div>
            <div class="ark-login-welcome-title">
                <div class="ark-login-welcome-text">WELCOME</div>
                <div class="ark-login-back-text">BACK</div>
                <div class="ark-login-title-line">
                    <div class="ark-login-line"></div>
                    <div class="ark-login-small-diamond">
                        <i class="fa-regular fa-gem"></i>
                    </div>
                    <div class="ark-login-line"></div>
                </div>
            </div>
            <p class="ark-login-description">
                Login to access your account<br>
                and continue your journey<br>
                with us.
            </p>
        </div>
    </div>

    <!-- RIGHT PANEL -->
    <div class="ark-login-right-panel">
        <div class="ark-login-form-box">

            <!-- EMAIL -->
            <div class="ark-login-field-wrap">
                <div class="ark-login-input-group">
                    <label>Email Address</label>
                    <div class="ark-login-input-wrapper <?= !empty($errors['email']) ? 'has-error' : '' ?>">
                        <input type="email"
                               name="email"
                               id="loginEmail"
                               placeholder="Enter your email"
                               value="<?= $old['email'] ?? '' ?>">
                    </div>
                </div>
                <?php if (!empty($errors['email'])): ?>
                    <span class="ark-field-error"><?= $errors['email'] ?></span>
                <?php endif; ?>
            </div>

            <!-- PASSWORD -->
            <div class="ark-login-field-wrap">
                <div class="ark-login-input-group">
                    <label>Password</label>
                    <div class="ark-login-input-wrapper <?= !empty($errors['password']) ? 'has-error' : '' ?>">
                        <input type="password"
                               name="password"
                               id="loginPassword"
                               placeholder="Enter your password">
                        <i class="fa-regular fa-eye" id="togglePassword"></i>
                    </div>
                </div>
                <?php if (!empty($errors['password'])): ?>
                    <span class="ark-field-error"><?= $errors['password'] ?></span>
                <?php endif; ?>
            </div>

            <!-- REMEMBER / FORGOT -->
            <div class="ark-login-remember-row">
                <label class="ark-login-remember-me">
                    <input type="checkbox" name="remember" id="remember">
                    Remember me
                </label>
                <a href="forgot-password.php" class="ark-login-forgot-password">
                    Forgot password?
                </a>
            </div>

            <!-- BUTTON -->
            <form method="POST" action="login.php" id="arkLoginForm" novalidate>
                <input type="hidden" name="email"    id="hiddenEmail">
                <input type="hidden" name="password" id="hiddenPassword">
                <input type="hidden" name="remember" id="hiddenRemember">

                <button type="submit" class="ark-login-btn">LOGIN</button>
            </form>

            <!-- SIGNUP -->
            <div class="ark-login-signup-text">
                Don't have an account?
                <a href="sign-up.php">Sign up</a>
            </div>

        </div>
    </div>
</div>

<script>
(function () {
    'use strict';

    

    // ── Element refs ─────────────────────────────────────────
    const emailInput    = document.getElementById('loginEmail');
    const passwordInput = document.getElementById('loginPassword');
    const rememberBox   = document.getElementById('remember');
    const toggleBtn     = document.getElementById('togglePassword');
    const form          = document.getElementById('arkLoginForm');

    // ── Password toggle ──────────────────────────────────────
    toggleBtn.addEventListener('click', function () {
        const isHidden = passwordInput.type === 'password';
        passwordInput.type = isHidden ? 'text' : 'password';
        this.classList.toggle('fa-eye',       isHidden);
        this.classList.toggle('fa-eye-slash', !isHidden);
    });

    // ── Helpers ──────────────────────────────────────────────
    function getWrap(input) {
        return input.closest('.ark-login-field-wrap');
    }

    function showError(input, message) {
        const wrapper = input.closest('.ark-login-input-wrapper');
        const wrap    = getWrap(input);
        if (!wrapper || !wrap) return;
        wrapper.classList.add('has-error');
        let span = wrap.querySelector('.ark-field-error');
        if (!span) {
            span = document.createElement('span');
            span.className = 'ark-field-error';
            wrap.appendChild(span);
        }
        span.textContent = message;
    }

    function clearError(input) {
        const wrapper = input.closest('.ark-login-input-wrapper');
        const wrap    = getWrap(input);
        if (!wrapper || !wrap) return;
        wrapper.classList.remove('has-error');
        const span = wrap.querySelector('.ark-field-error');
        if (span) span.textContent = '';
    }

    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    // ── Live blur validation ─────────────────────────────────
    emailInput.addEventListener('blur', function () {
        if (!this.value.trim()) {
            showError(this, 'Email address is required.');
        } else if (!isValidEmail(this.value.trim())) {
            showError(this, 'Please enter a valid email address.');
        } else {
            clearError(this);
        }
    });

    passwordInput.addEventListener('blur', function () {
        if (!this.value) {
            showError(this, 'Password is required.');
        } else {
            clearError(this);
        }
    });

    // ── Submit guard ─────────────────────────────────────────
    form.addEventListener('submit', function (e) {
        let valid = true;

        if (!emailInput.value.trim()) {
            showError(emailInput, 'Email address is required.'); valid = false;
        } else if (!isValidEmail(emailInput.value.trim())) {
            showError(emailInput, 'Please enter a valid email address.'); valid = false;
        } else {
            clearError(emailInput);
        }

        if (!passwordInput.value) {
            showError(passwordInput, 'Password is required.'); valid = false;
        } else {
            clearError(passwordInput);
        }

        if (!valid) {
            e.preventDefault();
            return;
        }

        // Pass values into the form's hidden fields before submit
        document.getElementById('hiddenEmail').value    = emailInput.value;
        document.getElementById('hiddenPassword').value = passwordInput.value;
        document.getElementById('hiddenRemember').value = rememberBox.checked ? 'on' : '';
    });

})();
</script>

</body>
</html>