<?php
include_once 'includes/config.php';
include_once 'includes/auth.php';
requireAdmin();

if (!isset($_GET["id"]) || empty(trim($_GET["id"]))) {
    header("location: admin_users.php");
    exit;
}

$user_id = trim($_GET["id"]);

// Preveri, če je uporabnik sam sebe
if ($user_id == $_SESSION["user_id"]) {
    header("location: admin_users.php?error=Ne morete izbrisati lastnega računa.");
    exit;
}

// Preveri, če ima uporabnik povezana popravila
$sql = "SELECT id FROM repairs WHERE reported_by = ? OR assigned_to = ?";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("ii", $user_id, $user_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        header("location: admin_users.php?error=Uporabnik ima povezana popravila in ga ni mogoče izbrisati.");
        exit;
    }
    $stmt->close();
}

// Brisanje uporabnika
$sql = "DELETE FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("i", $user_id);
    if ($stmt->execute()) {
        header("location: admin_users.php?success=Uporabnik je bil uspešno izbrisan.");
    } else {
        header("location: admin_users.php?error=Napaka pri brisanju uporabnika.");
    }
    $stmt->close();
} else {
    header("location: admin_users.php?error=Napaka pri pripravi poizvedbe.");
}
$conn->close();
?>