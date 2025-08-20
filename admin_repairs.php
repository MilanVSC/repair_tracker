<?php
include_once 'includes/config.php';
include_once 'includes/auth.php';
requireAdmin();

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
<h2>Upravljanje popravil</h2>

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
                <td><?php echo $repair["status"]; ?></td>
                <td><?php echo $repair["priority"]; ?></td>
                <td><?php echo htmlspecialchars($repair["reported_by_name"]); ?></td>
                <td><?php echo $repair["assigned_to_name"] ? htmlspecialchars($repair["assigned_to_name"]) : 'Brez'; ?></td>
                <td><?php echo $repair["reported_date"]; ?></td>
                <td>
                    <a href="view_repair.php?id=<?php echo $repair["id"]; ?>">Ogled</a>
                    <a href="admin_edit_repair.php?id=<?php echo $repair["id"]; ?>">Uredi</a>
                    <a href="admin_delete_repair.php?id=<?php echo $repair["id"]; ?>" onclick="return confirm('Ste prepričani?')">Izbriši</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include_once 'includes/footer.php'; ?>