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
    if ($action === 'create_module') {
        $module_name = trim($_POST['module_name'] ?? '');
        $module_code = trim($_POST['module_code'] ?? '');
        $errors = [];
        if ($module_name === '') $errors[] = 'Module name required';
        if ($module_code === '') $errors[] = 'Module code required';
        if (empty($errors)) {
            $stmt = $pdo->prepare('INSERT INTO modules (module_code, module_name, created_at) VALUES (?,?,NOW())');
            $stmt->execute([$module_code, $module_name]);
            $_SESSION['admin_success'] = 'Module added.';
            $_SESSION['admin_errors'] = [];
        } else {
            $_SESSION['admin_errors'] = $errors;
            $_SESSION['admin_success'] = '';
        }
    } elseif ($action === 'delete_module') {
        $module_id = (int)($_POST['module_id'] ?? 0);
        if ($module_id > 0) {
            try {
                $stmt = $pdo->prepare('DELETE FROM modules WHERE module_id = ?');
                $stmt->execute([$module_id]);
                $_SESSION['admin_success'] = 'Module deleted.';
                $_SESSION['admin_errors'] = [];
            } catch (PDOException $e) {
                $_SESSION['admin_errors'] = ['Cannot delete module: it may be referenced by posts.'];
                $_SESSION['admin_success'] = '';
            }
        }
    } elseif ($action === 'edit_module') {
        $module_id = (int)($_POST['module_id'] ?? 0);
        $module_name = trim($_POST['module_name'] ?? '');
        $module_code = trim($_POST['module_code'] ?? '');
        $errors = [];
        if ($module_id <= 0) $errors[] = 'Invalid module.';
        if ($module_name === '') $errors[] = 'Module name required';
        if ($module_code === '') $errors[] = 'Module code required';
        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare('UPDATE modules SET module_code = ?, module_name = ? WHERE module_id = ?');
                $stmt->execute([$module_code, $module_name, $module_id]);
                $_SESSION['admin_success'] = 'Module updated.';
                $_SESSION['admin_errors'] = [];
            } catch (PDOException $e) {
                $_SESSION['admin_errors'] = ['Unable to update module.'];
                $_SESSION['admin_success'] = '';
            }
        } else {
            $_SESSION['admin_errors'] = $errors;
            $_SESSION['admin_success'] = '';
        }
    }
}

// redirect back to admin UI
header('Location: admin.html.php');
exit;

?>
