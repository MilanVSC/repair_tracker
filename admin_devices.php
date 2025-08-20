<?php
include_once 'includes/config.php';
include_once 'includes/auth.php';
requireAdmin();

$devices = [];
$sql = "SELECT d.*, u.username as created_by_name 
        FROM devices d
        LEFT JOIN users u ON d.created_by = u.id
        ORDER BY d.name";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    $devices = $result->fetch_all(MYSQLI_ASSOC);
}
?>

<?php include_once 'includes/header.php'; ?>
<h2>Upravljanje naprav</h2>

<a href="admin_add_device.php">Dodaj novo napravo</a>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Ime</th>
            <th>Tip</th>
            <th>Serijska št.</th>
            <th>Datum nakupa</th>
            <th>Garancija</th>
            <th>Dodano</th>
            <th>Akcije</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($devices as $device): ?>
            <tr>
                <td><?php echo $device["id"]; ?></td>
                <td><?php echo htmlspecialchars($device["name"]); ?></td>
                <td><?php echo htmlspecialchars($device["type"]); ?></td>
                <td><?php echo htmlspecialchars($device["serial_number"]); ?></td>
                <td><?php echo $device["purchase_date"]; ?></td>
                <td><?php echo $device["warranty_expiry"]; ?></td>
                <td><?php echo $device["created_at"]; ?></td>
                <td>
                    <a href="admin_edit_device.php?id=<?php echo $device["id"]; ?>">Uredi</a>
                    <a href="admin_delete_device.php?id=<?php echo $device["id"]; ?>" onclick="return confirm('Ste prepričani?')">Izbriši</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include_once 'includes/footer.php'; ?>