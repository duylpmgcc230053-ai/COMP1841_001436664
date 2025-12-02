<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
include __DIR__ . '/../includes/header.php';

$success = false;
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $urgent = isset($_POST['urgent']) ? 'Yes' : 'No';

    if (!$name) $errors[] = 'Name required';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email required';
    if (!$subject) $errors[] = 'Subject required';
    if (!$message) $errors[] = 'Message required';

    if (empty($errors)) {
        $to = 'admin@example.com';
        $email_subject = 'Contact: ' . substr($name,0,50);
        $body = "From: $name\nEmail: $email\nSubject: $subject\nUrgent: $urgent\n\n$message";
        // mail() may not work on localhost; adjust in README
        $sent = mail($to, $email_subject, $body, "From: $email\r\nReply-To: $email");
        $success = $sent;
        if (!$sent) $errors[] = 'Mail sending failed (maybe not configured on localhost).';
    }
}
?>

<h2>Contact us</h2>
<?php if ($success) echo '<p style="color:green">Tin nhắn đã được gửi thành công.</p>'; ?>
<?php if ($errors) echo '<div style="color:red"><ul>' . implode('', array_map('e',$errors)) . '</ul></div>'; ?>

<form method="post" style="max-width: 600px; margin: auto; background: #f9f9f9; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
  <div style="margin-bottom: 15px;">
    <label for="name" style="display: block; font-weight: bold; margin-bottom: 5px;">Full name</label>
    <input id="name" name="name" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
  </div>
  <div style="margin-bottom: 15px;">
    <label for="email" style="display: block; font-weight: bold; margin-bottom: 5px;">Email</label>
    <input id="email" name="email" type="email" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
  </div>
  <div style="margin-bottom: 15px;">
    <label for="subject" style="display: block; font-weight: bold; margin-bottom: 5px;">Topic</label>
    <input id="subject" name="subject" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
  </div>
  <div style="margin-bottom: 15px;">
    <label for="message" style="display: block; font-weight: bold; margin-bottom: 5px;">Message content</label>
    <textarea id="message" name="message" rows="6" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;"></textarea>
  </div>
  <div style="margin-bottom: 15px;">
    <input type="checkbox" id="urgent" name="urgent" style="margin-right: 10px;">
    <label for="urgent" style="font-weight: bold;">Mark as urgent</label>
  </div>
  <div>
    <button type="submit" style="background-color: #4CAF50; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">Send Message</button>
  </div>
</form>

<?php include __DIR__ . '/../includes/footer.php'; ?>