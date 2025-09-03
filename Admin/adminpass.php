<?php
$admin_password = password_hash('admin_password', PASSWORD_BCRYPT);
echo $admin_password;
?>
