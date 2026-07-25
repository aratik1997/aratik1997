<?php
require_once __DIR__ . '/../includes/auth.php';
start_admin_session();
$_SESSION = [];
session_destroy();
header('Location: index.php');
exit;
