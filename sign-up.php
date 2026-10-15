<?php
session_start();
require_once 'includes/db.php';

// -- Helpers -- //

function sanitize(string $value): string {
    return htmlspecialchars(strip_tags(trim($value)));
}

$errors     = [];
$old        = [];  
$success    = false;

// -- Handle Post -- //
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Collect & sanitize
    $old['first_name'] = sanitize($_POST['first_name'] ?? '');
    $old['last_name']  = sanitize($_POST['last_name']  ?? '');
    $old['email']      = sanitize($_POST['email']      ?? '');
    $password          = $_POST['password']         ?? '';
    $confirm_password  = $_POST['confirm_password'] ?? '';
    $terms             = isset($_POST['terms']);

    // -- Server Side Validation -- //
    // First name
    if (empty($old['first_name'])) {
        $errors['first_name'] = 'First name is required.';
    } elseif (!preg_match('/^[A-Za-z\s\-\']{2,50}$/', $old['first_name'])) {
        $errors['first_name'] = 'First name must be 2–50 letters only.';
    }

    // Last name
    if (empty($old['last_name'])) {
        $errors['last_name'] = 'Last name is required.';
    } elseif (!preg_match('/^[A-Za-z\s\-\']{2,50}$/', $old['last_name'])) {
        $errors['last_name'] = 'Last name must be 2–50 letters only.';
    }

    // Email
    if (empty($old['email'])) {
        $errors['email'] = 'Email address is required.';
    } elseif (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }

    // Password
    if (empty($password)) {
        $errors['password'] = 'Password is required.';
    } elseif (strlen($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters.';
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $errors['password'] = 'Password must contain at least one uppercase letter.';
    } elseif (!preg_match('/[0-9]/', $password)) {
        $errors['password'] = 'Password must contain at least one number.';
    }

    // Confirm password
    if (empty($confirm_password)) {
        $errors['confirm_password'] = 'Please confirm your password.';
    } elseif ($password !== $confirm_password) {
        $errors['confirm_password'] = 'Passwords do not match.';
    }

    // Terms
    if (!$terms) {
        $errors['terms'] = 'You must agree to the Terms & Conditions.';
    }

 
    // -- Check For Duplicated Emails -- //
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $old['email']);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $errors['email'] = 'An account with this email already exists.';
        }
        $stmt->close();
    }


    // -- Insert New User -- //
    if (empty($errors)) {
        $hashed = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $conn->prepare(
            "INSERT INTO users (first_name, last_name, email, password) VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param("ssss", $old['first_name'], $old['last_name'], $old['email'], $hashed);

        if ($stmt->execute()) {
            // Auto-login after successful registration
            $_SESSION['user_id']   = $stmt->insert_id;
            $_SESSION['user_name'] = $old['first_name'];

            header("Location: index.php");
            exit;
        } else {
            $errors['general'] = 'Something went wrong. Please try again.';
        }
        $stmt->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Sign Up</title>
    <link rel="stylesheet" href="includes/style.css">
    <link rel="stylesheet" href="style-global.css">

    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;500;600;700&family=Pinyon+Script&family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        .ark-signup-input-group.has-error {
            border-color: #c0392b !important;
        }
        .ark-field-error {
            display: block;
            color: #9b1c1c;                        /* ← darker red */
            font-family: 'Montserrat', sans-serif;
            font-size: 0.7rem;
            font-weight: 500;                      /* ← bolder */
            letter-spacing: 0.04em;
            margin-top: 5px;
            margin-left: 2px;
        }
        .ark-general-error {
            background: #fdf0ef;
            border: 1px solid #c0392b;
            color: #9b1c1c;
            font-family: 'Montserrat', sans-serif;
            font-size: 0.75rem;
            font-weight: 500;
            letter-spacing: 0.04em;
            padding: 10px 14px;
            border-radius: 4px;
            margin-bottom: 14px;
            text-align: center;
        }
        .ark-signup-checkbox.has-error span {
            color: #9b1c1c;
        }
    </style>

</head>
<body>
    <section class="ark-signup-page">
        <!-- ========== IMAGE ========== -->
        <div class="ark-signup-image-side">
            <img src="images/sign-up/sign-up.png"
                alt="Arkangel & Co Sign Up">
        </div>

        <!-- ========== SIGN UP FORM ========== -->
        <div class="ark-signup-form-side">
            <header class="ark-signup-header">
                <p class="ark-signup-subtitle">Create Account</p>

                <div class="ark-signup-divider">
                    <span></span>
                    <i class="fa-regular fa-gem"></i>
                    <span></span>
                </div>

                <h1 class="ark-signup-title">SIGN UP</h1>

                <p class="ark-signup-description">
                    Join Arkangel &amp; Co and discover timeless elegance.
                </p>
            </header>

            <!-- ========== GENERAL ERROR ========== -->
            <?php if (!empty($errors['general'])): ?>
                <div class="ark-general-error">
                    <?= $errors['general'] ?>
                </div>
            <?php endif; ?>

            <form class="ark-signup-form" id="arkSignupForm" method="POST" action="sign-up.php" novalidate>

                <!-- ========== NAME ROW ========== -->
                <div class="ark-signup-name-row">
                    <div class="ark-signup-field-wrap">
                        <div class="ark-signup-input-group <?= !empty($errors['first_name']) ? 'has-error' : '' ?>">
                            <i class="fa-regular fa-user"></i>
                            <input type="text" name="first_name" id="first_name"
                                placeholder="First Name"
                                value="<?= $old['first_name'] ?? '' ?>"
                                autocomplete="given-name">
                        </div>
                        <?php if (!empty($errors['first_name'])): ?>
                            <span class="ark-field-error"><?= $errors['first_name'] ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="ark-signup-field-wrap">
                        <div class="ark-signup-input-group <?= !empty($errors['last_name']) ? 'has-error' : '' ?>">
                            <i class="fa-regular fa-user"></i>
                            <input type="text" name="last_name" id="last_name"
                                placeholder="Last Name"
                                value="<?= $old['last_name'] ?? '' ?>"
                                autocomplete="family-name">
                        </div>
                        <?php if (!empty($errors['last_name'])): ?>
                            <span class="ark-field-error"><?= $errors['last_name'] ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- ========== EMAIL ========== -->
                <div class="ark-signup-field-wrap">
                    <div class="ark-signup-input-group <?= !empty($errors['email']) ? 'has-error' : '' ?>">
                        <i class="fa-regular fa-envelope"></i>
                        <input type="email" name="email" id="email"
                            placeholder="Email Address"
                            value="<?= $old['email'] ?? '' ?>"
                            autocomplete="email">
                    </div>
                    <?php if (!empty($errors['email'])): ?>
                        <span class="ark-field-error"><?= $errors['email'] ?></span>
                    <?php endif; ?>
                </div>

                <!-- ========== PASSWORD ========== -->
                <div class="ark-signup-field-wrap">
                    <div class="ark-signup-input-group <?= !empty($errors['password']) ? 'has-error' : '' ?>">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" name="password" id="password"
                            placeholder="Password" autocomplete="new-password">
                        <button type="button" class="ark-password-toggle" aria-label="Toggle password visibility">
                            <i class="fa-regular fa-eye-slash"></i>
                        </button>
                    </div>
                    <?php if (!empty($errors['password'])): ?>
                        <span class="ark-field-error"><?= $errors['password'] ?></span>
                    <?php endif; ?>
                </div>

                <!-- ========== CONFIRM PASSWORD ========== -->
                <div class="ark-signup-field-wrap">
                    <div class="ark-signup-input-group <?= !empty($errors['confirm_password']) ? 'has-error' : '' ?>">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" name="confirm_password" id="confirm_password"
                            placeholder="Confirm Password" autocomplete="new-password">
                        <button type="button" class="ark-password-toggle" aria-label="Toggle confirm password visibility">
                            <i class="fa-regular fa-eye-slash"></i>
                        </button>
                    </div>
                    <?php if (!empty($errors['confirm_password'])): ?>
                        <span class="ark-field-error"><?= $errors['confirm_password'] ?></span>
                    <?php endif; ?>
                </div>


                <!-- ========== TERMS ========== -->
                <label class="ark-signup-checkbox <?= !empty($errors['terms']) ? 'has-error' : '' ?>">
                    <input type="checkbox" name="terms" id="terms">
                    <span>
                        I agree to the
                        <a href="terms&conditions.php">Terms & Conditions</a>
                        and
                        <a href="privacy-policy.php">Privacy Policy</a>
                    </span>
                </label>
                <?php if (!empty($errors['terms'])): ?>
                    <span class="ark-field-error" style="margin-bottom: 16px; display:block;"><?= $errors['terms'] ?></span>
                <?php endif; ?>


                <!-- ========== SUBMIT ========== -->
                <button type="submit" class="ark-signup-btn">
                    CREATE ACCOUNT
                </button>
            </form>

            <!-- ========== LOGIN LINK ========== -->
            <div class="ark-signup-login">
                Already have an account?
                <a href="login.php">Log In</a>
            </div>

        </div>
    </section>

<script>
(function () {
    'use strict';

    // -- Password toggle -- //
    document.querySelectorAll('.ark-password-toggle').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const input = this.closest('.ark-signup-input-group').querySelector('input');
            const icon  = this.querySelector('i');
            const isHidden = input.type === 'password';

            input.type = isHidden ? 'text' : 'password';
            icon.classList.toggle('fa-eye-slash', !isHidden);
            icon.classList.toggle('fa-eye',       isHidden);
        });
    });

    // -- Helpers-- //
    function getWrap(input) {
        return input.closest('.ark-signup-field-wrap');
    }

    function showError(input, message) {
        const group = input.closest('.ark-signup-input-group');
        const wrap  = input.closest('.ark-signup-field-wrap');
        if (!group || !wrap) return;
        group.classList.add('has-error');
        let span = wrap.querySelector('.ark-field-error');
        if (!span) {
            span = document.createElement('span');
            span.className = 'ark-field-error';
            wrap.appendChild(span);          
        }
        span.textContent = message;
    }

    function clearError(input) {
        const group = input.closest('.ark-signup-input-group');
        const wrap  = input.closest('.ark-signup-field-wrap');
        if (!group || !wrap) return;
        group.classList.remove('has-error');
        const span = wrap.querySelector('.ark-field-error');
        if (span) span.textContent = '';
    }

    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    function isValidName(name) {
        return /^[A-Za-z\s\-']{2,50}$/.test(name);
    }

    // -- Live validation -- //
    const firstName = document.getElementById('first_name');
    const lastName  = document.getElementById('last_name');
    const email     = document.getElementById('email');
    const password  = document.getElementById('password');
    const confirm   = document.getElementById('confirm_password');
    const terms     = document.getElementById('terms');

    firstName.addEventListener('blur', function () {
        if (!this.value.trim()) {
            showError(this, 'First name is required.');
        } else if (!isValidName(this.value.trim())) {
            showError(this, 'First name must be 2–50 letters only.');
        } else {
            clearError(this);
        }
    });

    lastName.addEventListener('blur', function () {
        if (!this.value.trim()) {
            showError(this, 'Last name is required.');
        } else if (!isValidName(this.value.trim())) {
            showError(this, 'Last name must be 2–50 letters only.');
        } else {
            clearError(this);
        }
    });

    email.addEventListener('blur', function () {
        if (!this.value.trim()) {
            showError(this, 'Email address is required.');
        } else if (!isValidEmail(this.value.trim())) {
            showError(this, 'Please enter a valid email address.');
        } else {
            clearError(this);
        }
    });

    password.addEventListener('blur', function () {
        const val = this.value;
        if (!val) {
            showError(this, 'Password is required.');
        } else if (val.length < 8) {
            showError(this, 'Password must be at least 8 characters.');
        } else if (!/[A-Z]/.test(val)) {
            showError(this, 'Password must contain at least one uppercase letter.');
        } else if (!/[0-9]/.test(val)) {
            showError(this, 'Password must contain at least one number.');
        } else {
            clearError(this);
        }
    });

    confirm.addEventListener('blur', function () {
        if (!this.value) {
            showError(this, 'Please confirm your password.');
        } else if (this.value !== password.value) {
            showError(this, 'Passwords do not match.');
        } else {
            clearError(this);
        }
    });

    // -- Client-side submit guard -- //
    document.getElementById('arkSignupForm').addEventListener('submit', function (e) {
        let valid = true;

        // First name
        if (!firstName.value.trim()) {
            showError(firstName, 'First name is required.'); valid = false;
        } else if (!isValidName(firstName.value.trim())) {
            showError(firstName, 'First name must be 2–50 letters only.'); valid = false;
        } else { clearError(firstName); }

        // Last name
        if (!lastName.value.trim()) {
            showError(lastName, 'Last name is required.'); valid = false;
        } else if (!isValidName(lastName.value.trim())) {
            showError(lastName, 'Last name must be 2–50 letters only.'); valid = false;
        } else { clearError(lastName); }

        // Email
        if (!email.value.trim()) {
            showError(email, 'Email address is required.'); valid = false;
        } else if (!isValidEmail(email.value.trim())) {
            showError(email, 'Please enter a valid email address.'); valid = false;
        } else { clearError(email); }

        // Password
        const pw = password.value;
        if (!pw) {
            showError(password, 'Password is required.'); valid = false;
        } else if (pw.length < 8) {
            showError(password, 'Password must be at least 8 characters.'); valid = false;
        } else if (!/[A-Z]/.test(pw)) {
            showError(password, 'Password must contain at least one uppercase letter.'); valid = false;
        } else if (!/[0-9]/.test(pw)) {
            showError(password, 'Password must contain at least one number.'); valid = false;
        } else { clearError(password); }

        // Confirm password
        if (!confirm.value) {
            showError(confirm, 'Please confirm your password.'); valid = false;
        } else if (confirm.value !== pw) {
            showError(confirm, 'Passwords do not match.'); valid = false;
        } else { clearError(confirm); }

        // Terms
        if (!terms.checked) {
            const label = terms.closest('.ark-signup-checkbox');
            if (label) label.classList.add('has-error');
            // Show or create error span after the label
            let termsErr = label.nextElementSibling;
            if (!termsErr || !termsErr.classList.contains('ark-field-error')) {
                termsErr = document.createElement('span');
                termsErr.className = 'ark-field-error';
                label.insertAdjacentElement('afterend', termsErr);
            }
            termsErr.textContent = 'You must agree to the Terms & Conditions.';
            valid = false;
        } else {
            const label = terms.closest('.ark-signup-checkbox');
            if (label) label.classList.remove('has-error');
            const termsErr = label.nextElementSibling;
            if (termsErr && termsErr.classList.contains('ark-field-error')) {
                termsErr.textContent = '';
            }
        }

        if (!valid) e.preventDefault();
    });

})();
</script>

</body>
</html>