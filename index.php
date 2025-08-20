<?php
include_once 'includes/config.php';
include_once 'includes/auth.php';

// Preusmeri na prijavo ali nadzorno ploščo
if (isLoggedIn()) {
    header("location: dashboard.php");
} else {
    header("location: login.php");
}
exit;
?>