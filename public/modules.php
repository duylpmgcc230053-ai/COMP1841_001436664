<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
include __DIR__ . '/../includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'create') {
        $code = trim($_POST['module_code'] ?? '');
        $name = trim($_POST['module_name'] ?? '');
        if ($code && $name) {
            $stmt = $pdo->prepare('INSERT INTO modules (module_code, module_name) VALUES (?,?)');
            $stmt->execute([$code, $name]);
        }
  } elseif ($_POST['action'] === 'delete') {
    $module_id = (int)($_POST['module_id'] ?? 0);
    if ($module_id > 0) {
      try {
        $stmt = $pdo->prepare('DELETE FROM modules WHERE module_id = ?');
        $stmt->execute([$module_id]);
      } catch (PDOException $e) {
        $error = 'Cannot delete module referenced by posts.';
      }
    }
  }
}

$modules = $pdo->query('SELECT * FROM modules ORDER BY created_at DESC')->fetchAll();
?>

<h2>Modules</h2>
<?php if (!empty($error)) echo '<p style="color:red">'.e($error).'</p>'; ?>
<ul>
<?php foreach($modules as $m): ?>
  <li><?php echo e($m['module_code']); ?> — <?php echo e($m['module_name']); ?>
    <form method="post" style="display:inline" onsubmit="return confirm('Delete module?');">
      <input type="hidden" name="action" value="delete">
      <input type="hidden" name="module_id" value="<?php echo $m['module_id']; ?>">
      <button type="submit">Delete</button>
    </form>
  </li>
<?php endforeach; ?>
</ul>

<h3>Add module</h3>
<form method="post">
  <input type="hidden" name="action" value="create">
  <div><label>Module code</label><input name="module_code" required></div>
  <div><label>Module name</label><input name="module_name" required></div>
  <div><button type="submit">Add</button></div>
</form>

<?php include __DIR__ . '/../includes/footer.php'; ?>