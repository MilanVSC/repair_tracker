<?php
include_once 'includes/config.php';
include_once 'includes/auth.php';
requireAdmin();

if (!isset($_GET["id"]) || empty(trim($_GET["id"]))) {
    header("location: admin_repairs.php");
    exit;
}

// ... koda za brisanje ...

if ($stmt->execute()) {
    header("location: admin_repairs.php?success=Popravilo je bilo uspešno izbrisano.");
} else {
    header("location: admin_repairs.php?error=Napaka pri brisanju popravila.");
}
?>