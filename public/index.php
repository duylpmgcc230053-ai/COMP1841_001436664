<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
include __DIR__ . '/../includes/header.php';
?>
<!-- Hero section (homepage) -->
<section class="hero">
  <div class="hero-left">
    <h1>Academic support 
      <br>for a brighter future.</br>
    </h1>
    
      <div class="hero-cta">
        <a class="cta-btn primary" href="users.php?action=signup">Sign up</a>
        <a class="cta-btn outline" href="users.php?action=login">Login</a>
      </div>
  </div>
  <div class="hero-right">
    <img src="assets/images/asking .png" alt="Illustration">
  </div>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>