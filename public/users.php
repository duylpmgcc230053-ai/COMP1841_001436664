<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
include __DIR__ . '/../includes/header.php';

if (!isset($_SESSION['user_id'])) {
		header('Location: login.php');
		exit;
}


$user_id = $_SESSION['user_id'];
$username = isset($_SESSION['username']) ? $_SESSION['username'] : '';

//Get this user's posts
$sql = "SELECT p.*, m.module_name FROM posts p JOIN modules m ON p.module_id = m.module_id WHERE p.user_id = ? ORDER BY p.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$posts = $stmt->fetchAll();

?>

<div style="display:flex; max-width:900px; margin:30px auto; gap:32px; align-items:flex-start;">
	<!-- Sidebar user info -->
	<aside style="min-width:180px; background:#f7f7fb; border-radius:10px; padding:24px 12px 18px 12px; box-shadow:0 2px 8px rgba(0,0,0,0.03); display:flex; flex-direction:column; align-items:center;">
		<div style="width:70px; height:70px; border-radius:50%; background:#4f54dd; color:white; display:flex; align-items:center; justify-content:center; font-size:2.2em; font-weight:bold; margin-bottom:12px;">
			<?= $username ? strtoupper($username[0]) : '?' ?>
		</div>
		<div style="font-size:1.1em; font-weight:600; color:#333; text-align:center; word-break:break-all;">
			<?= e($username) ?>
		</div>
	</aside>

	<!-- Main content: user posts -->
	<div class="user-posts" style="flex:1;">
		<h2 style="text-align:center;">Your post</h2>
		<?php if (empty($posts)): ?>
			<div class="card empty-card">
				<div class="empty-icon">📰</div>
				<div class="empty-text">You have no posts yet.</div>
			</div>
		<?php else: ?>
			<?php foreach ($posts as $post): ?>
				<article class="post card" style="margin-bottom:24px; padding:18px; border:1px solid #ddd; border-radius:8px;">
					<div style="font-size:0.95em; color:#777; font-weight:600;">Subject: <?= e($post['module_name']) ?> — <?= $post['created_at'] ?></div>
					<?php if (!empty($post['image_path'])): ?>
						<img src="<?= e($post['image_path']) ?>" alt="Post image" style="max-width:100%; border-radius:8px; margin-top:10px;">
					<?php endif; ?>
					<p style="margin-top:10px; font-size:1.1em; color:#333;"><?= nl2br(e($post['body'])) ?></p>
					<div class="actions" style="margin-top:10px; display:flex; gap:10px;">
						<a href="post_edit.php?post_id=<?= $post['post_id'] ?>" style="background:#4f54dd; color:white; text-decoration:none; padding:8px 12px; border-radius:4px; cursor:pointer;">Edit</a>
						<form action="post_delete.php" method="post" style="display:inline" onsubmit="return confirm('Delete this post');">
							<input type="hidden" name="post_id" value="<?= $post['post_id'] ?>">
							<button type="submit" style="background:#4f54dd; color:white; border:none; padding:8px 12px; border-radius:4px; cursor:pointer;">Delete</button>
						</form>
					</div>
				</article>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>