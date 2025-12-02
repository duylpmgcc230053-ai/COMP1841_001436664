<?php
//Start session and load configuration (need to check config.php again)
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

//Variables required for the form
$errors = [];
$first_last_name = '';
$email = '';

// ==========================================================
// 1. LOGIC login (POST REQUEST)
// ==========================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email is required.';
    if ($password === '') $errors[] = 'Password is required.';

    if (empty($errors)) {
        // Fetch user by email (users table uses `user_id` as PK)
        $stmt = $pdo->prepare('SELECT user_id AS id, email, password_hash, username, role FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && !empty($user['password_hash']) && password_verify($password, $user['password_hash'])) {
            // Authentication successful
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            // store role and use role-based admin redirect
            $_SESSION['role'] = $user['role'] ?? 'user';
            if ($_SESSION['role'] === 'admin') {
                header('Location: admin.html.php');
                exit;
            }
            header('Location: questions.php');
            exit;
        } else {
            $errors[] = 'Invalid email or password.';
        }
    }
}
include __DIR__ . '/../includes/header.php'; // Load header after processing POST

// ==========================================================
// 2. = FORM HTML
// ==========================================================
?>

<div class="welcome-title">Welcome back!</div>

<div class="auth-container">
    <div class="login-form-box">
    <p class="subtitle">Log in to your Student-Q&amp;A account</p>

        <?php if ($errors):  ?>
          <div class="errors" style="color:red; text-align: left; margin-bottom: 15px;">
              <ul><?php foreach($errors as $e) echo '<li>'.e($e).'</li>'; ?></ul>
          </div>
        <?php endif; ?>


    <!-- Login form (submits to server) -->
    <form method="post" class="login-form" novalidate>
            <div class="form-group">
                <label for="email">Work email</label>
                <input id="email" name="email" type="email" required placeholder="john@company.com" value="<?= e($email) ?>">
            </div>

            <div class="form-group">
                <label for="password">Your password</label>
                <input id="password" name="password" type="password" required>
                <button type="button" class="eye-toggle" aria-label="Toggle password" onclick="togglePassword()"></button>
            </div>

            <button type="submit" class="login-btn">Log in</button>
        </form>



    </div>
</div>

                <div style="text-align:center; margin-top:18px; color:#666; font-size:15px;">
    <div>New here? <a href="public/signup.php">Sign up now</a></div>
</div>

<script>
// Small helper to toggle password visibility (UI only)
function togglePassword(){
    var p = document.getElementById('password');
    if(!p) return;
    p.type = p.type === 'password' ? 'text' : 'password';
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>