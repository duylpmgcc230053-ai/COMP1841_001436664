<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
// only allow the designated admin account to view this page

// require role-based admin access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header('Location: questions.php');
  exit;
}
include __DIR__ . '/../includes/header.php';

// read flash messages from admin modules/users/posts handlers and categorize
$raw_errors = $_SESSION['admin_errors'] ?? [];
$raw_success = $_SESSION['admin_success'] ?? '';
unset($_SESSION['admin_errors'], $_SESSION['admin_success']);

// Prepare per-section messages
$module_errors = [];
$user_errors = [];
$post_errors = [];
$module_success = '';
$user_success = '';
$post_success = '';

// categorize errors (case-insensitive matches)
foreach ($raw_errors as $e) {
  $lc = strtolower($e);
  if (strpos($lc, 'module') !== false || strpos($lc, 'subject') !== false) {
    $module_errors[] = $e;
  } elseif (strpos($lc, 'user') !== false) {
    $user_errors[] = $e;
  } else {
    // default to module area if we can't classify
    $module_errors[] = $e;
  }
}

// categorize success message
if ($raw_success) {
  $lc = strtolower($raw_success);
  if (strpos($lc, 'module') !== false || strpos($lc, 'subject') !== false) {
    $module_success = $raw_success;
  } elseif (strpos($lc, 'user') !== false) {
    $user_success = $raw_success;
  } elseif (strpos($lc, 'post') !== false || strpos($lc, 'article') !== false) {
    $post_success = $raw_success;
  } else {
    $module_success = $raw_success;
  }
}

// (post delete moved to adminposts.php)

// fetch modules, posts, and users
$modules = $pdo->query('SELECT module_id, module_code, module_name, created_at FROM modules ORDER BY module_id ASC')->fetchAll();
$posts = $pdo->query("SELECT p.post_id, p.body, p.image_path, p.created_at, u.username, m.module_name FROM posts p JOIN users u ON p.user_id = u.user_id JOIN modules m ON p.module_id = m.module_id ORDER BY p.created_at DESC")->fetchAll();
$users = $pdo->query("SELECT user_id, username, email, created_at FROM users ORDER BY user_id ASC")->fetchAll();
?>

<div style="padding:24px; max-width:1100px; margin:0 auto;">
  <h2 style="margin-bottom:18px;">Admin Management Page</h2>

  <div style="background:#fff; border-radius:8px; padding:18px; box-shadow:0 6px 18px rgba(0,0,0,0.05); margin-bottom:20px;">
    <h3>Add new subject</h3>
    <?php if ($module_success): ?><div style="color:green; margin-bottom:10px"><?php echo e($module_success); ?></div><?php endif; ?>
    <?php if ($module_errors): ?><div style="color:#b00020; margin-bottom:10px"><?php foreach($module_errors as $e) echo '<div>'.e($e).'</div>'; ?></div><?php endif; ?>
  <form method="post" action="admin_modules.php" style="margin-top:10px;">
      <input type="hidden" name="action" value="create_module">
      <div style="margin-bottom:12px;">
        <label style="display:block; font-weight:600; margin-bottom:6px;">Subject name</label>
  <input name="module_name" placeholder="Enter subject name" style="width:100%; padding:10px; border:1px solid #e6e6e6; border-radius:6px;">
      </div>
      <div style="margin-bottom:12px;">
        <label style="display:block; font-weight:600; margin-bottom:6px;">Subject code</label>
  <input name="module_code" placeholder="Enter subject code (e.g., MATH101)" style="width:100%; padding:10px; border:1px solid #e6e6e6; border-radius:6px;">
      </div>
      <button type="submit" style="background:#2b8cff; color:#fff; padding:10px 14px; border-radius:6px; border:none;">Add a subject</button>
    </form>
  </div>

  <div style="background:#fff; border-radius:8px; padding:18px; box-shadow:0 6px 18px rgba(0,0,0,0.05); margin-bottom:20px;">
    <h3>List of subjects</h3>
    <table style="width:100%; border-collapse:collapse; margin-top:12px;">
      <thead>
        <tr style="background:#f7f7f7; text-align:left;">
          <th style="padding:12px; width:60px;">ID</th>
          <th style="padding:12px;">Subject name</th>
          <th style="padding:12px;">Description/Code</th>
          <th style="padding:12px; width:120px; text-align:center;">Operation</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($modules as $m): ?>
          <tr style="border-top:1px solid #eee;">
            <td style="padding:12px; vertical-align:top"><?= e($m['module_id']) ?></td>
            <td style="padding:12px; vertical-align:top"><?= e($m['module_name']) ?></td>
            <td style="padding:12px; vertical-align:top"><?= e($m['module_code']) ?></td>
            <td style="padding:12px; text-align:center; vertical-align:top">
              <a href="admin_edit_module.php?module_id=<?= e($m['module_id']) ?>" style="display:inline-block; margin-right:8px; background:#4f54dd; color:#fff; text-decoration:none; padding:8px 10px; border-radius:6px;">edit</a>
              <form method="post" action="admin_modules.php" style="display:inline" onsubmit="return confirm('Delete this subject?');">
                <input type="hidden" name="action" value="delete_module">
                <input type="hidden" name="module_id" value="<?= e($m['module_id']) ?>">
                <button type="submit" style="background:#e55353; color:#fff; border:none; padding:8px 10px; border-radius:6px;">delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>


  <div style="background:#fff; border-radius:8px; padding:18px; box-shadow:0 6px 18px rgba(0,0,0,0.05); margin-bottom:20px;">
    <h3>User management</h3>
    <?php if ($user_success): ?><div style="color:green; margin-bottom:10px"><?php echo e($user_success); ?></div><?php endif; ?>
    <?php if ($user_errors): ?><div style="color:#b00020; margin-bottom:10px"><?php foreach($user_errors as $e) echo '<div>'.e($e).'</div>'; ?></div><?php endif; ?>
    <table style="width:100%; border-collapse:collapse; margin-top:12px;">
      <thead>
        <tr style="background:#f7f7f7; text-align:left;">
          <th style="padding:12px; width:60px;">ID</th>
          <th style="padding:12px;">Username</th>
          <th style="padding:12px;">Email</th>
          <th style="padding:12px;">Password</th>
          <th style="padding:12px; width:140px;">Created at</th>
          <th style="padding:12px; width:120px; text-align:center;">Operation</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($users as $u): ?>
          <tr style="border-top:1px solid #eee;">
              <form method="post" action="admin_users.php" style="display:contents">
              <input type="hidden" name="user_id" value="<?= e($u['user_id']) ?>">
              <td style="padding:12px; vertical-align:top"><?= e($u['user_id']) ?></td>
              <td style="padding:12px; vertical-align:top">
                <input name="username" value="<?= e($u['username']) ?>" style="width:120px; padding:4px; border:1px solid #ccc; border-radius:4px;">
              </td>
              <td style="padding:12px; vertical-align:top">
                <input name="email" value="<?= e($u['email']) ?>" style="width:180px; padding:4px; border:1px solid #ccc; border-radius:4px;">
              </td>
              <td style="padding:12px; vertical-align:top">
                <input name="password" type="password" placeholder="Leave blank to keep" style="width:160px; padding:4px; border:1px solid #ccc; border-radius:4px;">
              </td>
              <td style="padding:12px; vertical-align:top"><?= e($u['created_at']) ?></td>
              <td style="padding:12px; text-align:center; vertical-align:top">
                <button type="submit" name="action" value="edit_user" style="background:#4f54dd; color:#fff; border:none; padding:6px 10px; border-radius:6px; margin-right:6px;">Save</button>
                <button type="submit" name="action" value="delete_user" style="background:#e55353; color:#fff; border:none; padding:6px 10px; border-radius:6px;" onclick="return confirm('Delete this user?');">Delete</button>
              </td>
            </form>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div style="background:#fff; border-radius:8px; padding:18px; box-shadow:0 6px 18px rgba(0,0,0,0.05);">
    <h3>List of articles</h3>
    <?php if ($post_success): ?><div style="color:green; margin-bottom:10px"><?php echo e($post_success); ?></div><?php endif; ?>
    <?php if ($post_errors): ?><div style="color:#b00020; margin-bottom:10px"><?php foreach($post_errors as $e) echo '<div>'.e($e).'</div>'; ?></div><?php endif; ?>
    <table style="width:100%; border-collapse:collapse; margin-top:12px;">
      <thead>
        <tr style="background:#f7f7f7; text-align:left;">
          <th style="padding:12px; width:60px;">ID</th>
          <th style="padding:12px;">Title / Content</th>
          <th style="padding:12px; width:180px;">Author</th>
          <th style="padding:12px; width:160px;">Subject</th>
          <th style="padding:12px; width:140px;">Date posted</th>
          <th style="padding:12px; width:120px; text-align:center;">Operation</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($posts as $p): ?>
          <tr style="border-top:1px solid #eee;">
            <td style="padding:12px; vertical-align:top"><?= e($p['post_id']) ?></td>
            <td style="padding:12px; vertical-align:top"><?= nl2br(e(substr($p['body'],0,120))) ?><?php if (strlen($p['body'])>120) echo '...'; ?></td>
            <td style="padding:12px; vertical-align:top"><?= e($p['username']) ?></td>
            <td style="padding:12px; vertical-align:top"><?= e($p['module_name']) ?></td>
            <td style="padding:12px; vertical-align:top"><?= e($p['created_at']) ?></td>
            <td style="padding:12px; text-align:center; vertical-align:top">
              <form method="post" action="admin_posts.php" style="display:inline" onsubmit="return confirm('Delete this post?');">
                <input type="hidden" name="action" value="delete_post">
                <input type="hidden" name="post_id" value="<?= e($p['post_id']) ?>">
                <button type="submit" style="background:#e55353; color:#fff; border:none; padding:8px 10px; border-radius:6px;">delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
