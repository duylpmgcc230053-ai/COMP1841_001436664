<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// only admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header('Location: questions.php');
  exit;
}

$module_id = (int)($_GET['module_id'] ?? 0);
if ($module_id <= 0) {
    header('Location: admin.html.php');
    exit;
}

// fetch module
$stmt = $pdo->prepare('SELECT module_id, module_code, module_name FROM modules WHERE module_id = ? LIMIT 1');
$stmt->execute([$module_id]);
$module = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$module) {
    header('Location: admin.html.php');
    exit;
}

include __DIR__ . '/../includes/header.php';
?>
<div style="max-width:700px; margin:24px auto;">
  <h2>Edit subject</h2>
  <?php if (!empty($_SESSION['admin_errors'])): ?>
    <div style="color:#b00020; margin-bottom:10px"><?php foreach($_SESSION['admin_errors'] as $e) echo '<div>'.e($e).'</div>'; ?></div>
    <?php unset($_SESSION['admin_errors']); endif; ?>
  <?php if (!empty($_SESSION['admin_success'])): ?>
    <div style="color:green; margin-bottom:10px"><?php echo e($_SESSION['admin_success']); unset($_SESSION['admin_success']); ?></div>
  <?php endif; ?>

  <form method="post" action="admin_modules.php">
    <input type="hidden" name="action" value="edit_module">
    <input type="hidden" name="module_id" value="<?= e($module['module_id']) ?>">

    <div style="margin-bottom:12px;">
      <label style="display:block; font-weight:600; margin-bottom:6px;">Subject name</label>
      <input name="module_name" value="<?= e($module['module_name']) ?>" style="width:100%; padding:10px; border:1px solid #e6e6e6; border-radius:6px;">
    </div>

    <div style="margin-bottom:12px;">
      <label style="display:block; font-weight:600; margin-bottom:6px;">Subject code</label>
      <input name="module_code" value="<?= e($module['module_code']) ?>" style="width:100%; padding:10px; border:1px solid #e6e6e6; border-radius:6px;">
    </div>

    <div style="display:flex; gap:10px;">
      <button type="submit" style="background:#2b8cff; color:#fff; padding:10px 14px; border-radius:6px; border:none;">Save changes</button>
      <a href="admin.html.php" style="display:inline-block; padding:10px 14px; border-radius:6px; border:1px solid #ccc; color:#333; text-decoration:none;">Cancel</a>
    </div>
  </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
