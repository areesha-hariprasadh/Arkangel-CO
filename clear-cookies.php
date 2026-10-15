<?php
setcookie('remember_token', '', time() - 3600, '/');
session_start();
session_destroy();
echo "Cleared. <a href='login.php'>Go to Login</a>";