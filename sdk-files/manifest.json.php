<?php
  header('Content-Type: application/json');
  header('X-Robots-Tag: none');
?>
{
  "gcm_sender_id": "<?php echo urlencode($_GET['gcm_sender_id']); ?>",
  "gcm_user_visible_only": true
}
