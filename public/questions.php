<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
include __DIR__ . '/../includes/header.php';

// Handle post creation directly in this file
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['comment_body'])) {
  $body = trim($_POST['body'] ?? '');
  // use the logged-in user's id from the session to avoid FK issues
  $user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
  $module_id = (int)($_POST['module_id'] ?? 0);

  $errors = [];
  if ($body === '') $errors[] = 'Content is required';
  if ($user_id <= 0) $errors[] = 'Invalid user session';

  $image_path = null;
  if (!empty($_FILES['image']['name'])) {
    $upload = uploadImage($_FILES['image']);
    if ($upload['success']) $image_path = $upload['path'];
    else $errors[] = $upload['error'];
  }

  if (empty($errors)) {
    $stmt = $pdo->prepare('INSERT INTO posts (body, user_id, module_id, image_path) VALUES (?,?,?,?)');
    $stmt->execute([$body, $user_id, $module_id ?: null, $image_path]);
    header('Location: questions.php');
    exit;
  }
}

// fetch modules for the create form
$modules = $pdo->query('SELECT module_id, module_name FROM modules ORDER BY module_name')->fetchAll();

// Optional module filter from GET
$filter_module = (int)($_GET['module_id'] ?? 0);

// Build posts query with optional module filter
$where = '';
$params = [];
if ($filter_module > 0) {
  $where = ' WHERE p.module_id = ?';
  $params[] = $filter_module;
}

$sql = "SELECT p.*, u.username, m.module_name 
  FROM posts p
  JOIN users u ON p.user_id = u.user_id
  JOIN modules m ON p.module_id = m.module_id" . $where . " ORDER BY p.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$posts = $stmt->fetchAll();

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_body'], $_POST['post_id'])) {
    $comment_body = trim($_POST['comment_body']);
    $post_id = (int)$_POST['post_id'];
    $user_id = $_SESSION['user_id'] ?? 0;

    if ($comment_body !== '' && $user_id > 0) {
        $stmt = $pdo->prepare('INSERT INTO comments (post_id, user_id, body, created_at) VALUES (?, ?, ?, NOW())');
        $stmt->execute([$post_id, $user_id, $comment_body]);
        header('Location: questions.php');
        exit;
    }
}

$sql_comments = "SELECT c.*, u.username FROM comments c JOIN users u ON c.user_id = u.user_id WHERE c.post_id = ? ORDER BY c.created_at DESC";
?>

<div class="questions-grid">
  <aside class="create-box">
    <div class="card">
      <h3>Create new post</h3>
      <form method="post" enctype="multipart/form-data" novalidate>
        <div class="form-row">
          <label for="module_id">Subject</label>
          <select id="module_id" name="module_id">
            <option value="">-- Select a subject --</option>
            <?php foreach($modules as $m) echo '<option value="'.e($m['module_id']).'">'.e($m['module_name']).'</option>'; ?>
          </select>
        </div>
        <div class="form-row">
          <label for="body">Content</label>
          <textarea id="body" name="body" rows="6" placeholder="Post content"></textarea>
        </div>
        <div class="form-row">
          <label for="image">Photo (optional)</label>
          <input id="image" name="image" type="file" accept="image/*">
        </div>
        <div class="form-row">
          <button class="btn btn-green" type="submit">Post an article</button>
        </div>
      </form>
    </div>
  </aside>

  <section class="feed">
    <!-- Module search above posts -->
    <div style="max-width:900px; margin:0 auto 16px; display:flex; gap:8px; align-items:center;">
      <form method="get" style="display:flex; gap:8px; width:100%;">
        <select name="module_id" style="padding:8px; border:1px solid #ddd; border-radius:6px;">
          <option value="">All subjects</option>
          <?php foreach($modules as $m): ?>
            <option value="<?= e($m['module_id']) ?>" <?php if($filter_module===(int)$m['module_id']) echo 'selected'; ?>><?= e($m['module_name']) ?></option>
          <?php endforeach; ?>
        </select>
        <button type="submit" style="padding:8px 12px; background:#4f54dd; color:#fff; border:none; border-radius:6px;">Search</button>
        <a href="questions.php" style="padding:8px 12px; border:1px solid #ddd; border-radius:6px; text-decoration:none; color:#333;">Reset</a>
      </form>
    </div>
    <div class="feed-list">
      <?php if (empty($posts)): ?>
        <div class="card empty-card">
          <div class="empty-icon">📰</div>
          <div class="empty-text">There are no posts yet. Create the first one!</div>
        </div>
      <?php else: ?>
        <?php foreach ($posts as $post): ?>
          <article class="post card" style="display:flex; flex-direction:column; align-items:center; border:1px solid #ddd; border-radius:8px; padding:16px; margin-bottom:16px;">
            <div style="display:flex; align-items:center; gap:12px;">
              <div style="width:50px; height:50px; border-radius:50%; background:#4f54dd; color:white; display:flex; align-items:center; justify-content:center; font-weight:bold;">
                <?= strtoupper($post['username'][0]) ?>
              </div>
              <div>
                <div style="font-size:0.9em; color:#777; font-weight:600;">By <?= e($post['username']) ?> — Subject: <?= e($post['module_name']) ?> — <?= $post['created_at'] ?></div>
              </div>
            </div>
            <?php if (!empty($post['image_path'])): ?>
              <img src="<?= e($post['image_path']) ?>" alt="Post image" style="max-width:100%; border-radius:8px; margin-top:10px;">
            <?php endif; ?>
            <p style="margin-top:10px; font-size:1em; color:#333;"><?= nl2br(e(substr($post['body'],0,300))) ?><?php if (strlen($post['body'])>300) echo '...'; ?></p>
            <div class="actions" style="margin-top:10px; display:flex; gap:10px;">
              <?php if ($post['user_id'] === ($_SESSION['user_id'] ?? 0)): ?>
                <a href="post_edit.php?post_id=<?= $post['post_id'] ?>" style="background:#4f54dd; color:white; text-decoration:none; padding:8px 12px; border-radius:4px; cursor:pointer;">Edit</a>
                <form action="post_delete.php" method="post" style="display:inline" onsubmit="return confirm('Confirm delete?');">
                  <input type="hidden" name="post_id" value="<?= $post['post_id'] ?>">
                  <button type="submit" style="background:#4f54dd; color:white; border:none; padding:8px 12px; border-radius:4px; cursor:pointer;">Delete</button>
                </form>
              <?php endif; ?>
            </div>

            <!-- Comments Section -->
            <div class="comments" style="margin-top:20px; width:100%;">
              <h4 style="margin-bottom:10px; font-size:1.1em; color:#333;">Comments</h4>
              <?php
              $stmt_comments = $pdo->prepare($sql_comments);
              $stmt_comments->execute([$post['post_id']]);
              $comments = $stmt_comments->fetchAll();
              ?>
              <?php if (empty($comments)): ?>
                <p style="font-size:0.9em; color:#777;">No comments yet. Be the first to comment!</p>
              <?php else: ?>
                <ul style="list-style:none; padding:0;">
                  <?php foreach ($comments as $comment): ?>
                    <li style="margin-bottom:8px; font-size:0.9em; color:#333;"><strong><?= e($comment['username']) ?>:</strong> <?= e($comment['body']) ?> <em style="color:#777;">(<?= $comment['created_at'] ?>)</em></li>
                  <?php endforeach; ?>
                </ul>
              <?php endif; ?>

              <!-- Add Comment Form -->
              <form method="post" style="margin-top:10px; display:flex; gap:10px; align-items:center;">
                <input type="hidden" name="post_id" value="<?= $post['post_id'] ?>">
                <textarea name="comment_body" rows="3" placeholder="Add a comment..." style="flex:1; border:1px solid #ddd; border-radius:4px; padding:8px; font-size:0.9em;"></textarea>
                <button type="submit" style="background:#4f54dd; color:white; border:none; padding:8px 12px; border-radius:4px; cursor:pointer;">Comment</button>
              </form>
            </div>
          </article>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </section>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
