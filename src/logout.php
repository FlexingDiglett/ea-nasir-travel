<?php
require_once __DIR__ . '/classes/User.php';
$auth = new User();
$auth->logout();

header("Location: index.php");
exit;
?>