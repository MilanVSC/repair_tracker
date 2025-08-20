<?php
include_once 'includes/config.php';
include_once 'includes/auth.php';
requireAdmin();

if (!isset($_GET["id"]) || empty(trim($_GET["id"]))) {
    header("location: admin_comments.php");
    exit;
}

// ... koda za brisanje ...

if ($stmt->execute()) {
    header("location: admin_comments.php?success=Komentar je bil uspešno izbrisan.");
} else {
    header("location: admin_comments.php?error=Napaka pri brisanju komentarja.");
}
?>