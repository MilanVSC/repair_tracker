<?php
include_once 'includes/config.php';
include_once 'includes/auth.php';
requireLogin();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["repair_id"])) {
    $repair_id = trim($_POST["repair_id"]);
    $comment = trim($_POST["comment"]);

    // Preveri ali popravilo obstaja
    $sql = "SELECT id FROM repairs WHERE id = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $repair_id);
        $stmt->execute();
        $stmt->store_result();

        // VSI prijavljeni uporabniki lahko komentirajo VSA popravila
        if ($stmt->num_rows == 1 && !empty($comment)) {
            // Vstavi komentar
            $insert_sql = "INSERT INTO comments (repair_id, user_id, comment) VALUES (?, ?, ?)";

            if ($insert_stmt = $conn->prepare($insert_sql)) {
                $insert_stmt->bind_param("iis", $repair_id, $_SESSION["user_id"], $comment);
                $insert_stmt->execute();
                $insert_stmt->close();
            }
        }
        $stmt->close();
    }
}
header("location: view_repair.php?id=" . $repair_id);
exit;
?>