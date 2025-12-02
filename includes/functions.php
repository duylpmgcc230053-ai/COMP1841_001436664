<?php
// includes/functions.php
function e($s) {
    return htmlspecialchars($s, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8');
}

function uploadImage(array $file) {
    $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/gif'=>'gif'];
    $max = 2 * 1024 * 1024;
    if ($file['error'] !== UPLOAD_ERR_OK) return ['success'=>false,'error'=>'Upload error'];
    if ($file['size'] > $max) return ['success'=>false,'error'=>'File too large'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    if (!isset($allowed[$mime])) return ['success'=>false,'error'=>'Invalid file type'];
    $ext = $allowed[$mime];
    $name = bin2hex(random_bytes(8)) . '.' . $ext;
    $dest_dir = __DIR__ . '/../public/assets/uploads/';
    if (!is_dir($dest_dir)) mkdir($dest_dir, 0755, true);
    $dest = $dest_dir . $name;
    if (!move_uploaded_file($file['tmp_name'], $dest)) return ['success'=>false,'error'=>'Failed to move file'];
    return ['success'=>true,'path'=>'assets/uploads/' . $name];
}
?>