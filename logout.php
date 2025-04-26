<?php
require_once 'includes/init.php';

// Logout user
$auth->logout();

// Redirect to login page
header('Location: login.php');
exit;
?>