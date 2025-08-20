<?php
include_once 'includes/config.php';
include_once 'includes/auth.php';
requireLogin();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["repair_id"])) {
    $repair_id = trim($_POST["repair_id"]);
    $comment = trim($_POST["comment"]);

    if (!empty($comment)) {
        $sql = "INSERT INTO comments (repair_id, user_id, comment) VALUES (?, ?, ?)";
        
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("iis", $repair_id, $_SESSION["user_id"], $comment);
            $stmt->execute();
            $stmt->close();
        }
    }
}
header("location: view_repair.php?id=" . $repair_id);
exit;
?>