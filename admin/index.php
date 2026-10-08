<?php
require_once __DIR__ . '/auth.php';
requireAdmin();

header('Location: dashboard.php');
exit;
