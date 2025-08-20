<?php
include_once 'includes/config.php';
include_once 'includes/auth.php';
requireLogin();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["repair_id"])) {
    $repair_id = trim($_POST["repair_id"]);

    // Preveri ali je uporabnik tehnik
    if (!isTechnician()) {
        header("location: view_repair.php?id=" . $repair_id . "&error=Samo tehniki lahko prevzamejo popravila.");
        exit;
    }

    // Preveri ali popravilo obstaja in ni že dodeljeno
    $sql = "SELECT id, assigned_to, status FROM repairs WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $repair_id);
        $stmt->execute();
        $stmt->bind_result($id, $assigned_to, $status);
        $stmt->fetch();
        $stmt->close();

        if (!$id) {
            header("location: all_repairs.php?error=Popravilo ne obstaja.");
            exit;
        }

        if ($assigned_to) {
            header("location: view_repair.php?id=" . $repair_id . "&error=Popravilo je že dodeljeno drugemu tehniku.");
            exit;
        }

        if ($status == "completed" || $status == "cancelled") {
            header("location: view_repair.php?id=" . $repair_id . "&error=Popravila v statusu 'Zaključeno' ali 'Preklicano' ni mogoče prevzeti.");
            exit;
        }

        // Posodobi popravilo - dodeli tehniku in spremeni status v "in_progress"
        $update_sql = "UPDATE repairs SET assigned_to = ?, status = 'in_progress' WHERE id = ?";
        if ($update_stmt = $conn->prepare($update_sql)) {
            $update_stmt->bind_param("ii", $_SESSION["user_id"], $repair_id);

            if ($update_stmt->execute()) {
                // Dodaj v zgodovino statusov
                $history_sql = "INSERT INTO status_history (repair_id, old_status, new_status, changed_by, notes) 
                                VALUES (?, ?, ?, ?, ?)";
                if ($history_stmt = $conn->prepare($history_sql)) {
                    $notes = "Tehnik je sam prevzel popravilo";
                    $history_stmt->bind_param("issss", $repair_id, $status, "in_progress", $_SESSION["user_id"], $notes);
                    $history_stmt->execute();
                    $history_stmt->close();
                }

                header("location: view_repair.php?id=" . $repair_id . "&success=Uspešno ste prevzeli popravilo.");
            } else {
                header("location: view_repair.php?id=" . $repair_id . "&error=Napaka pri prevzemu popravila.");
            }
            $update_stmt->close();
        }
    }
} else {
    header("location: all_repairs.php");
}
exit;
?>