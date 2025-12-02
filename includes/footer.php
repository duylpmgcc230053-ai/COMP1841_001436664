</main>
<?php
// Hide footer on auth routes (standalone login or users.php?action=login or signup)
 $current = basename($_SERVER['PHP_SELF']);
 $isLoginRoute = ($current === 'login.php') || ($current === 'users.php' && (isset($_GET['action']) && in_array($_GET['action'], ['login','signup'])));
if (! $isLoginRoute):
?>
<footer>
</footer>
  <!-- footer intentionally left blank (copyright removed) -->
</footer>
<?php endif; ?>
</body>
</html>
