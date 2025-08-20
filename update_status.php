<?php
include_once 'includes/config.php';
include_once 'includes/auth.php';
requireLogin();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["repair_id"])) {
    $repair_id = trim($_POST["repair_id"]);
    $new_status = trim($_POST["status"]);
    $notes = isset($_POST["notes"]) ? trim($_POST["notes"]) : "";
    $assigned_to = isset($_POST["assigned_to"]) ? trim($_POST["assigned_to"]) : NULL;

    // Preveri pravice
    $sql = "SELECT status, assigned_to FROM repairs WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $repair_id);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows == 1) {
            $stmt->bind_result($current_status, $current_assigned);
            $stmt->fetch();
            
            // Preveri, če ima uporabnik pravice za spremembo statusa
            $can_update = false;
            if (isAdmin()) {
                $can_update = true;
            } elseif (isTechnician() && ($current_assigned == $_SESSION["user_id"] || $new_status == "reported")) {
                $can_update = true;
            }
            
            if ($can_update) {
                // Posodobi status popravila
                $update_sql = "UPDATE repairs SET status = ?, assigned_to = ?";
                if ($new_status == "completed") {
                    $update_sql .= ", completed_date = NOW()";
                } elseif ($current_status == "completed" && $new_status != "completed") {
                    $update_sql .= ", completed_date = NULL";
                }
                $update_sql .= " WHERE id = ?";
                
                if ($update_stmt = $conn->prepare($update_sql)) {
                    $update_stmt->bind_param("sii", $new_status, $assigned_to, $repair_id);
                    $update_stmt->execute();
                    $update_stmt->close();
                    
                    // Dodaj v zgodovino statusov
                    $history_sql = "INSERT INTO status_history (repair_id, old_status, new_status, changed_by, notes) 
                                    VALUES (?, ?, ?, ?, ?)";
                    if ($history_stmt = $conn->prepare($history_sql)) {
                        $history_stmt->bind_param("issss", $repair_id, $current_status, $new_status, $_SESSION["user_id"], $notes);
                        $history_stmt->execute();
                        $history_stmt->close();
                    }
                }
            }
        }
        $stmt->close();
    }
}
header("location: view_repair.php?id=" . $repair_id);
exit;
?>