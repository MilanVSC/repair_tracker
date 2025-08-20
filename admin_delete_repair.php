<?php
include_once 'includes/config.php';
include_once 'includes/auth.php';
requireAdmin();

if (!isset($_GET["id"]) || empty(trim($_GET["id"]))) {
    header("location: admin_repairs.php");
    exit;
}

$repair_id = trim($_GET["id"]);

// Brisanje komentarjev povezanih s popravilom
$sql = "DELETE FROM comments WHERE repair_id = ?";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("i", $repair_id);
    $stmt->execute();
    $stmt->close();
}

// Brisanje zgodovine statusov
$sql = "DELETE FROM status_history WHERE repair_id = ?";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("i", $repair_id);
    $stmt->execute();
    $stmt->close();
}

// Brisanje slik popravila
$sql = "DELETE FROM repair_images WHERE repair_id = ?";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("i", $repair_id);
    $stmt->execute();
    $stmt->close();
}

// Brisanje popravila
$sql = "DELETE FROM repairs WHERE id = ?";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("i", $repair_id);
    if ($stmt->execute()) {
        header("location: admin_repairs.php?success=Popravilo je bilo uspešno izbrisano.");
    } else {
        header("location: admin_repairs.php?error=Napaka pri brisanju popravila.");
    }
    $stmt->close();
} else {
    header("location: admin_repairs.php?error=Napaka pri pripravi poizvedbe.");
}
$conn->close();
?>