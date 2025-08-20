<?php
include 'includes/config.php';
include 'includes/auth.php';
requireAdmin();

if (!isset($_GET["id"]) || empty(trim($_GET["id"]))) {
    header("location: admin_devices.php");
    exit;
}

$device_id = trim($_GET["id"]);

// Preveri, če ima naprava povezana popravila
$sql = "SELECT id FROM repairs WHERE device_id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $device_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        header("location: admin_devices.php?error=Naprava ima povezana popravila in je ni mogoče izbrisati.");
        exit;
    }
    $stmt->close();
}

// Brisanje naprave
$sql = "DELETE FROM devices WHERE id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $device_id);
    if ($stmt->execute()) {
        header("location: admin_devices.php?success=Naprava je bila uspešno izbrisana.");
    } else {
        header("location: admin_devices.php?error=Napaka pri brisanju naprave.");
    }
    $stmt->close();
}
$conn->close();
?>