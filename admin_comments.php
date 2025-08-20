<?php
include_once 'includes/config.php';
include_once 'includes/auth.php';
requireAdmin();

$comments = [];
$sql = "SELECT c.*, u.username, r.title as repair_title, r.id as repair_id
        FROM comments c
        JOIN users u ON c.user_id = u.id
        JOIN repairs r ON c.repair_id = r.id
        ORDER BY c.created_at DESC";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    $comments = $result->fetch_all(MYSQLI_ASSOC);
}
?>

<?php include_once 'includes/header.php'; ?>
<h2>Upravljanje komentarjev</h2>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Popravilo</th>
            <th>Uporabnik</th>
            <th>Komentar</th>
            <th>Datum</th>
            <th>Akcije</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($comments as $comment): ?>
            <tr>
                <td><?php echo $comment["id"]; ?></td>
                <td><a href="view_repair.php?id=<?php echo $comment["repair_id"]; ?>"><?php echo htmlspecialchars($comment["repair_title"]); ?></a></td>
                <td><?php echo htmlspecialchars($comment["username"]); ?></td>
                <td><?php echo nl2br(htmlspecialchars(substr($comment["comment"], 0, 100) . (strlen($comment["comment"]) > 100 ? '...' : ''))); ?></td>
                <td><?php echo $comment["created_at"]; ?></td>
                <td>
                    <a href="admin_delete_comment.php?id=<?php echo $comment["id"]; ?>" onclick="return confirm('Ste prepričani?')">Izbriši</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include_once 'includes/footer.php'; ?>