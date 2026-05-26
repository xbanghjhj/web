<?php
require_once 'config/database.php';
$email = 'xuanbangnguyen15205@gmail.com';
executeQuery("DELETE FROM users WHERE email = ?", 's', [$email]);
echo "Deleted\n";
?>
