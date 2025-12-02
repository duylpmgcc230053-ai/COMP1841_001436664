<?php
require_once __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/src/SMTP.php';
require_once __DIR__ . '/../PHPMailer/src/Exception.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$status = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $subject = trim($_POST['subject'] ?? '');
  $message = trim($_POST['message'] ?? '');
  if ($subject && $message) {
    $mail = new PHPMailer(true);
    try {
      $mail->isSMTP();
      $mail->Host       = 'smtp.gmail.com';
      $mail->SMTPAuth   = true;
      $mail->Username   = 'duy0942997697@gmail.com';
      $mail->Password   = 'eent owew nscr wgxu';
      $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
      $mail->Port       = 587;
      $mail->setFrom('duy0942997697@gmail.com', 'StudentQ&A');
      $mail->addAddress('duy0942997697@gmail.com');
      $mail->isHTML(false);
      $mail->Subject = $subject;
      $mail->Body    = $message;
      $mail->send();
      $status = 'Message sent!';
    } catch (Exception $e) {
      $status = 'Failed to send: ' . $mail->ErrorInfo;
    }
  } else {
    $status = 'Please fill in all fields.';
  }
}
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<style>
  /* Keep the sendmail form styles minimal and scoped to the page */
  .sendmail-container { max-width:700px; margin:24px auto; padding:20px; background:#fff; border-radius:8px; box-shadow:0 6px 18px rgba(0,0,0,0.06); }
  .sendmail-header { text-align:center; margin-bottom:18px; }
  .sendmail-status { background:#d4edda; color:#155724; padding:12px; border-radius:6px; margin-bottom:16px; display:<?php echo $status ? 'block' : 'none'; ?>; }
  .sendmail .form-group { margin-bottom:12px; }
  .sendmail input, .sendmail textarea { width:100%; padding:10px; border:1px solid #e6e6e6; border-radius:6px; }
  .sendmail button { background:#4f54dd; color:#fff; border:none; padding:10px 16px; border-radius:6px; cursor:pointer; }
</style>

<main class="sendmail">
  <div class="sendmail-container">
    <div class="sendmail-header">
      <h2>Contact / Send mail</h2>
      <p style="color:#666; margin:0">Use this form to send a message.</p>
    </div>

    <div class="sendmail-status"><?php echo htmlspecialchars($status); ?></div>

    <form method="post">
      <div class="form-group">
        <input type="text" name="subject" id="subject" placeholder="Subject:" required value="<?php echo isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : ''; ?>">
      </div>
      <div class="form-group">
        <textarea name="message" id="message" placeholder="Message:" required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
      </div>
      <button type="submit">Send Email</button>
    </form>
  </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>