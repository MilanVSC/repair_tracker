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
            <th>Akcije</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($repairs as $repair): ?>
            <tr>
                <td><?php echo $repair["id"]; ?></td>
                <td><?php echo htmlspecialchars($repair["device_name"]); ?></td>
                <td><?php echo htmlspecialchars($repair["title"]); ?></td>
                <td>
                    <span class="status-<?php echo $repair["status"]; ?>">
                        <?php
                        $status_text = [
                            'reported' => 'Prijavljeno',
                            'in_progress' => 'V teku',
                            'completed' => 'Zaključeno',
                            'cancelled' => 'Preklicano'
                        ];
                        echo $status_text[$repair["status"]] ?? $repair["status"];
                        ?>
                    </span>
                </td>
                <td>
                    <span class="priority-<?php echo $repair["priority"]; ?>">
                        <?php
                        $priority_text = [
                            'low' => 'Nizka',
                            'medium' => 'Srednja',
                            'high' => 'Visoka',
                            'critical' => 'Kritična'
                        ];
                        echo $priority_text[$repair["priority"]] ?? $repair["priority"];
                        ?>
                    </span>
                </td>
                <td><?php echo htmlspecialchars($repair["reported_by_name"]); ?></td>
                <td><?php echo $repair["assigned_to_name"] ? htmlspecialchars($repair["assigned_to_name"]) : 'Brez'; ?></td>
                <td><?php echo date('d.m.Y H:i', strtotime($repair["reported_date"])); ?></td>
                <td>
                    <a href="view_repair.php?id=<?php echo $repair["id"]; ?>">Ogled</a>
                    <?php if (isAdmin() || $repair["reported_by"] == $_SESSION["user_id"]): ?>
                        <a href="admin_edit_repair.php?id=<?php echo $repair["id"]; ?>">Uredi</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

<?php include_once 'includes/footer.php'; ?>