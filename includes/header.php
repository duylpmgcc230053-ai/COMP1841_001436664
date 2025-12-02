<?php
// ensure session is available (config.php normally starts session)
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
$current = basename($_SERVER['PHP_SELF']);
$hideAdminLinks = ($current === 'admin.html.php' && isset($_SESSION['role']) && $_SESSION['role'] === 'admin');
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Student Q&A</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    /* Header nav highlight on click */
    nav a.nav-link { padding:4px 6px; text-decoration:none; color:inherit; border-radius:4px; }
    nav a.nav-link.active { font-weight:700; background:#e8eefc; color:#123; }
  </style>
</head>
<body>
<header>
<div style="display:flex; justify-content:space-between; align-items:center; padding:12px;">
  <h1 style="font-size:1.5em; font-family:sans-serif; margin:0;">Student <span style="color:#4f54dd;">Q&amp;A</span></h1>
  <nav style="display:flex; gap:20px; font-size:1em;">
    <?php if (! $hideAdminLinks): ?>
      <a class="nav-link" href="questions.php">Questions</a>
      <a class="nav-link" href="users.php">User</a>
      <a class="nav-link" href="sendmail.php">Contact</a>
    <?php endif; ?>
  </nav>
  <div style="display:flex; gap:10px; align-items:center; position:relative;">
    <?php if (isset($_SESSION['username'])): ?>
      <div style="width:40px; height:40px; border-radius:50%; background:#4f54dd; color:white; display:flex; align-items:center; justify-content:center; font-weight:bold; cursor:pointer;" onclick="toggleDropdown()">
        <?= strtoupper($_SESSION['username'][0]) ?>
      </div>
      <div id="dropdown" style="display:none; position:absolute; top:50px; right:0; background:white; border:1px solid #ddd; border-radius:5px; box-shadow:0 2px 5px rgba(0,0,0,0.1);">
        <a href="logout.php" style="display:block; padding:10px; text-decoration:none; color:#333;">Log out</a>
      </div>
    <?php else: ?>
      <a href="login.php" style="text-decoration:none;">Log in</a>
      <a href="signup.php" style="background-color:blue; color:white; padding:5px 10px; border-radius:5px; text-decoration:none;">Sign up</a>
    <?php endif; ?>
  </div>
</div>
</header>
<main>
<script>
function toggleDropdown() {
  var dropdown = document.getElementById('dropdown');
  dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
}
// nav link active toggle and persistence
(function(){
  try {
    var links = document.querySelectorAll('nav a.nav-link');
    links.forEach(function(a){
      a.addEventListener('click', function(){
        links.forEach(function(x){ x.classList.remove('active'); });
        a.classList.add('active');
        try { localStorage.setItem('activeNav', a.getAttribute('href')); } catch(e){}
      });
    });
    // restore on load
    var active = null;
    try { active = localStorage.getItem('activeNav'); } catch(e){}
    if (active) {
      links.forEach(function(a){ if (a.getAttribute('href')===active) a.classList.add('active'); });
    } else {
      // mark current page link as active by href match
      var p = window.location.pathname.split('/').pop();
      links.forEach(function(a){ if (a.getAttribute('href') === p) a.classList.add('active'); });
    }
  } catch(e){}
})();
</script>
