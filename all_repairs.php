<?php
include_once 'includes/config.php';
include_once 'includes/auth.php';
requireLogin();

$repairs = [];
$sql = "SELECT r.*, d.name as device_name, u1.username as reported_by_name, u2.username as assigned_to_name
        FROM repairs r
        JOIN devices d ON r.device_id = d.id
        JOIN users u1 ON r.reported_by = u1.id
        LEFT JOIN users u2 ON r.assigned_to = u2.id
        ORDER BY r.reported_date DESC";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    $repairs = $result->fetch_all(MYSQLI_ASSOC);
}
?>


<?php include_once 'includes/header.php'; ?>
    <h2>Vsa popravila</h2>

    <div style="margin-bottom: 20px;">
        <a href="add_repair.php" class="button">Dodaj novo popravilo</a>
        <a href="dashboard.php" class="button">Nazaj na nadzorno ploščo</a>
    </div>

<?php if (isset($_GET['success'])): ?>
    <div class="success"><?php echo htmlspecialchars($_GET['success']); ?></div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <div class="error"><?php echo htmlspecialchars($_GET['error']); ?></div>
<?php endif; ?>

    <table>
        <thead>
        <tr>
            <th>ID</th>
            <th>Naprava</th>
            <th>Naslov</th>
            <th>Status</th>
            <th>Prioriteta</th>
            <th>Prijavil</th>
            <th>Dodeljeno</th>
            <th>Datum prijave</th>
            <th>Stevilo komentarjev</th>
            <th>Akcije</th>
        </tr>
        </thead>
        <tbody>
        <?php
        // Najprej pridobi vsa popravila
        $sql = "SELECT r.*, d.name as device_name, u1.username as reported_by_name, u2.username as assigned_to_name
            FROM repairs r
            JOIN devices d ON r.device_id = d.id
            JOIN users u1 ON r.reported_by = u1.id
            LEFT JOIN users u2 ON r.assigned_to = u2.id
            ORDER BY r.reported_date DESC";

        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            // Pripravi array za ID-je popravil
            $repair_ids = [];
            $repairs_data = [];

            while ($row = $result->fetch_assoc()) {
                $repair_ids[] = $row["id"];
                $repairs_data[$row["id"]] = $row;
            }

            // Pridobi števila komentarjev za vsa popravila naenkrat
            $comment_counts = [];
            if (!empty($repair_ids)) {
                $placeholders = implode(',', array_fill(0, count($repair_ids), '?'));
                $count_sql = "SELECT repair_id, COUNT(*) as count FROM comments 
                          WHERE repair_id IN ($placeholders) GROUP BY repair_id";

                if ($count_stmt = $conn->prepare($count_sql)) {
                    $count_stmt->bind_param(str_repeat('i', count($repair_ids)), ...$repair_ids);
                    $count_stmt->execute();
                    $count_result = $count_stmt->get_result();

                    while ($count_row = $count_result->fetch_assoc()) {
                        $comment_counts[$count_row["repair_id"]] = $count_row["count"];
                    }
                    $count_stmt->close();
                }
            }

            // Prikaži podatke
            foreach ($repairs_data as $repair_id => $row) {
                $comment_count = $comment_counts[$repair_id] ?? 0;

                echo "<tr>";
                echo "<td>" . $row["id"] . "</td>";
                echo "<td>" . htmlspecialchars($row["device_name"]) . "</td>";
                echo "<td>" . htmlspecialchars($row["title"]) . "</td>";
                echo "<td>
                    <span class='status-" . $row["status"] . "'>
                        " . [
                                'reported' => 'Prijavljeno',
                                'in_progress' => 'V teku',
                                'completed' => 'Zaključeno',
                                'cancelled' => 'Preklicano'
                        ][$row["status"]] . "
                    </span>
                  </td>";
                echo "<td>
                    <span class='priority-" . $row["priority"] . "'>
                        " . [
                                'low' => 'Nizka',
                                'medium' => 'Srednja',
                                'high' => 'Visoka',
                                'critical' => 'Kritična'
                        ][$row["priority"]] . "
                    </span>
                  </td>";
                echo "<td>" . htmlspecialchars($row["reported_by_name"]) . "</td>";
                echo "<td>" . ($row["assigned_to_name"] ? htmlspecialchars($row["assigned_to_name"]) : 'Brez') . "</td>";
                echo "<td>" . date('d.m.Y H:i', strtotime($row["reported_date"])) . "</td>";
                echo "<td>
                    <span class='comment-count' title='Število komentarjev'>
                        " . $comment_count . "
                    </span>
                  </td>";
                echo "<td>
                    <a href='view_repair.php?id=" . $row["id"] . "'>Ogled</a>";

                if (isAdmin() || $row["reported_by"] == $_SESSION["user_id"]):
                    echo " <a href='admin_edit_repair.php?id=" . $row["id"] . "'>Uredi</a>";
                endif;

                if (isTechnician() && empty($row["assigned_to"]) && $row["status"] != "completed" && $row["status"] != "cancelled"):
                    echo " <form action='take_repair.php' method='post' style='display: inline;'>
                        <input type='hidden' name='repair_id' value='" . $row["id"] . "'>
                        <button type='submit' style='background: none; border: none; color: #28a745; cursor: pointer; text-decoration: underline;'>Prevzemi</button>
                      </form>";
                endif;

                echo "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='10'>Ni najdenih popravil</td></tr>";
        }
        ?>
        </tbody>
    </table>

<?php include_once 'includes/footer.php'; ?>