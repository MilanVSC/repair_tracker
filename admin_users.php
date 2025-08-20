<?php
include_once 'includes/config.php';
include_once 'includes/auth.php';
requireAdmin();

$users = [];
$sql = "SELECT id, username, email, full_name, role, created_at FROM users ORDER BY username";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    $users = $result->fetch_all(MYSQLI_ASSOC);
}
?>

<?php include_once 'includes/header.php'; ?>
<h2>Upravljanje uporabnikov</h2>

<a href="admin_add_user.php">Dodaj novega uporabnika</a>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Uporabniško ime</th>
            <th>Email</th>
            <th>Polno ime</th>
            <th>Vloga</th>
            <th>Registriran</th>
            <th>Akcije</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?php echo $user["id"]; ?></td>
                <td><?php echo htmlspecialchars($user["username"]); ?></td>
                <td><?php echo htmlspecialchars($user["email"]); ?></td>
                <td><?php echo htmlspecialchars($user["full_name"]); ?></td>
                <td><?php echo $user["role"]; ?></td>
                <td><?php echo $user["created_at"]; ?></td>
                <td>
                    <a href="admin_edit_user.php?id=<?php echo $user["id"]; ?>">Uredi</a>
                    <?php if ($user["id"] != $_SESSION["user_id"]): ?>
                        <a href="admin_delete_user.php?id=<?php echo $user["id"]; ?>" onclick="return confirm('Ste prepričani?')">Izbriši</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include_once 'includes/footer.php'; ?>