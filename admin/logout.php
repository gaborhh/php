<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

unset($_SESSION['user_id']);
header("Location: index.php");
exit();
?>
