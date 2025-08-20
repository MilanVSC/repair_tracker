<?php
include_once 'includes/config.php';
include_once 'includes/auth.php';
requireAdmin();

if (!isset($_GET["id"]) || empty(trim($_GET["id"]))) {
    header("location: admin_comments.php");
    exit;
}

$comment_id = trim($_GET["id"]);

// Brisanje komentarja
$sql = "DELETE FROM comments WHERE id = ?";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("i", $comment_id);
    if ($stmt->execute()) {
        header("location: admin_comments.php?success=Komentar je bil uspešno izbrisan.");
    } else {
        header("location: admin_comments.php?error=Napaka pri brisanju komentarja.");
    }
    $stmt->close();
} else {
    header("location: admin_comments.php?error=Napaka pri pripravi poizvedbe.");
}
$conn->close();
?>