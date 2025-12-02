<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
include __DIR__ . '/../includes/header.php';

$errors = [];
$success_message = '';


//  PROCESS POST FORM
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validation 
    if (!$full_name) $errors[] = 'Full name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email is required.';
    if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters.';
    
    if (empty($errors)) {
        // TẠO CẢ HAI: password hash VÀ password text
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $password_text = $password; // THÊM DÒNG NÀY để lưu password dạng text

        try {
            // CẬP NHẬT: Thêm cột 'password' vào query
            $sql = "INSERT INTO users (username, email, password_hash, password, role) 
                    VALUES (:username, :email, :password_hash, :password, :role)";
            $stmt = $pdo->prepare($sql);

            // Bind parameters by name
            $stmt->bindParam(':username', $full_name, PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':password_hash', $password_hash, PDO::PARAM_STR);
            $stmt->bindParam(':password', $password_text, PDO::PARAM_STR); // THÊM DÒNG NÀY
            $role = 'user'; // Default role is user
            $stmt->bindParam(':role', $role, PDO::PARAM_STR);

           // Execute query
            $stmt->execute();
            header('Location: login.php');
            exit;

        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $errors[] = 'Email or username already exists.';
            } else {
                $errors[] = 'Registration failed due to a server error.';
            }
        }
    }
}
?>
<div class="auth-container">
    <div class="signup-form-box">
        <h2>Create a free account</h2>
        <p class="recommendation">We recommend using your work or school email to keep things separate</p>
        
        <?php if (!empty($errors)): ?>
            <div class="errors" style="color:red; text-align:left; margin-bottom:15px;">
                <ul>
                    <?php foreach($errors as $err): ?>
                        <li><?php echo htmlspecialchars($err); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <?php if ($success_message): ?>
            <div style="color:green; margin-bottom:12px; text-align:left;">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>
        
        <form method="post" autocomplete="off">
            <div class="form-group">
                <label>First and last name</label>
                <input name="full_name" type="text" maxlength="50" value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
                <span class="char-limit">50</span>
            </div>
            <div class="form-group">
                <label>Work email</label>
                <input name="email" type="email" placeholder="john@company.com" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Choose a password</label>
                <input id="signup-password" name="password" type="password">
                <button type="button" class="eye-toggle" onclick="togglePassword('signup-password')"></button>
                <div class="password-hint">At least 8 characters</div>
            </div>
            <div style="margin-top:12px;">
                <button type="submit" class="login-btn">Sign up</button>
            </div>
        </form>
    </div>
</div>

<script>
function togglePassword(id){
    var el = document.getElementById(id);
    if(!el) return;
    el.type = (el.type === 'password') ? 'text' : 'password';
}
</script>

<?php
include __DIR__ . '/../includes/footer.php';
?>