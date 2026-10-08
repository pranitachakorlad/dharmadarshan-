<?php
require_once __DIR__ . '/includes/functions.php';

unset($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_email']);
setFlash('success', 'You have been logged out.');
header('Location: index.php');
exit;
