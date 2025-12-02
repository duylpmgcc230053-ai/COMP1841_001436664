<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// role check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header('Location: questions.php');
  exit;
}

// initialize flash
$_SESSION['admin_errors'] = $_SESSION['admin_errors'] ?? [];
$_SESSION['admin_success'] = $_SESSION['admin_success'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'delete_user') {
        $user_id = (int)($_POST['user_id'] ?? 0);
        if ($user_id > 0) {
            // prevent deleting your own admin account accidentally
            if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $user_id) {
                $_SESSION['admin_errors'] = ['You cannot delete your own account.'];
                $_SESSION['admin_success'] = '';
            } else {
                $pdo->beginTransaction();
                try {
                    // delete comments authored by the user
                    $stmt = $pdo->prepare('DELETE FROM comments WHERE user_id = ?');
                    $stmt->execute([$user_id]);

                    // remove image files for posts authored by this user (avoid orphan files)
                    $stmt = $pdo->prepare('SELECT image_path FROM posts WHERE user_id = ?');
                    $stmt->execute([$user_id]);
                    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($rows as $r) {
                        if (!empty($r['image_path'])) {
                            @unlink(__DIR__ . '/' . $r['image_path']);
                        }
                    }

                    // delete posts authored by the user
                    $stmt = $pdo->prepare('DELETE FROM posts WHERE user_id = ?');
                    $stmt->execute([$user_id]);

                    // delete user
                    $stmt = $pdo->prepare('DELETE FROM users WHERE user_id = ?');
                    $stmt->execute([$user_id]);
                    $pdo->commit();
                    $_SESSION['admin_success'] = 'User deleted.';
                    $_SESSION['admin_errors'] = [];
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $_SESSION['admin_errors'] = ['Failed to delete user.'];
                    $_SESSION['admin_success'] = '';
                }
            }
        }
    } elseif ($action === 'edit_user') {
        $user_id = (int)($_POST['user_id'] ?? 0);
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $errors = [];
        if ($user_id <= 0) $errors[] = 'Invalid user.';
        if ($username === '') $errors[] = 'Username required';
        if ($email === '') $errors[] = 'Email required';
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email is required.';
        if (empty($errors)) {
            try {
                // If password provided, hash and update it as well
                if ($password !== '') {
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare('UPDATE users SET username = ?, email = ?, password_hash = ? WHERE user_id = ?');
                    $stmt->execute([$username, $email, $password_hash, $user_id]);
                } else {
                    $stmt = $pdo->prepare('UPDATE users SET username = ?, email = ? WHERE user_id = ?');
                    $stmt->execute([$username, $email, $user_id]);
                }
                $_SESSION['admin_success'] = 'User updated.';
                $_SESSION['admin_errors'] = [];
            } catch (PDOException $e) {
                $_SESSION['admin_errors'] = ['Unable to update user.'];
                $_SESSION['admin_success'] = '';
            }
        } else {
            $_SESSION['admin_errors'] = $errors;
            $_SESSION['admin_success'] = '';
        }
    }
}

header('Location: admin.html.php');
exit;
