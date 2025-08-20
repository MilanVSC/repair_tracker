<?php
include_once 'includes/config.php';
include_once 'includes/auth.php';
requireLogin();

if (!isset($_GET["id"]) || empty(trim($_GET["id"]))) {
    header("location: dashboard.php");
    exit;
}

$repair_id = trim($_GET["id"]);
$repair = $comments = [];

// Pridobi podatke o popravilu
$sql = "SELECT r.*, d.name as device_name, u1.username as reported_by_name, u2.username as assigned_to_name
        FROM repairs r
        JOIN devices d ON r.device_id = d.id
        JOIN users u1 ON r.reported_by = u1.id
        LEFT JOIN users u2 ON r.assigned_to = u2.id
        WHERE r.id = ?";
        
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $repair_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $repair = $result->fetch_assoc();
    } else {
        header("location: dashboard.php");
        exit;
    }
    $stmt->close();
}

// Pridobi komentarje
$sql = "SELECT c.*, u.username, u.role
        FROM comments c
        JOIN users u ON c.user_id = u.id
        WHERE c.repair_id = ?
        ORDER BY c.created_at DESC";
        
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $repair_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $comments = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Pridobi slike za popravilo
$images = [];
$sql = "SELECT ri.*, u.username 
        FROM repair_images ri
        JOIN users u ON ri.user_id = u.id
        WHERE ri.repair_id = ?
        ORDER BY ri.uploaded_at DESC";
        
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $repair_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $images = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
?>

<?php include_once 'includes/header.php'; ?>
<h2>Podrobnosti popravila #<?php echo $repair["id"]; ?></h2>

<div class="repair-details">
    <h3><?php echo htmlspecialchars($repair["title"]); ?></h3>
    <p><strong>Naprava:</strong> <?php echo htmlspecialchars($repair["device_name"]); ?></p>
    <p><strong>Status:</strong> <?php echo $repair["status"]; ?></p>
    <p><strong>Prioriteta:</strong> <?php echo $repair["priority"]; ?></p>
    <p><strong>Prijavil:</strong> <?php echo htmlspecialchars($repair["reported_by_name"]); ?></p>
    <?php if ($repair["assigned_to_name"]): ?>
        <p><strong>Dodeljeno:</strong> <?php echo htmlspecialchars($repair["assigned_to_name"]); ?></p>
    <?php endif; ?>
    <p><strong>Datum prijave:</strong> <?php echo $repair["reported_date"]; ?></p>
    <?php if ($repair["completed_date"]): ?>
        <p><strong>Datum zaključka:</strong> <?php echo $repair["completed_date"]; ?></p>
    <?php endif; ?>
    <p><strong>Opis:</strong></p>
    <p><?php echo nl2br(htmlspecialchars($repair["description"])); ?></p>
</div>

<!-- Galerija slik -->
<div class="repair-images">
    <h3>Slike popravila</h3>
    
    <?php if (!empty($images)): ?>
        <div class="image-gallery">
            <?php foreach ($images as $image): ?>
                <div class="image-item">
                    <img src="<?php echo $image['image_path']; ?>" alt="<?php echo htmlspecialchars($image['image_name']); ?>" style="max-width: 200px; max-height: 200px;">
                    <p>Dodal: <?php echo htmlspecialchars($image['username']); ?><br>
                    <?php echo $image['uploaded_at']; ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>Ni naloženih slik.</p>
    <?php endif; ?>
    
    <a href="add_repair_images.php?id=<?php echo $repair['id']; ?>">Dodaj slike</a>
</div>

<?php if (isAdmin() || isTechnician()): ?>
<div class="repair-actions">
    <h3>Upravljanje popravila</h3>
    <form action="update_status.php" method="post">
        <input type="hidden" name="repair_id" value="<?php echo $repair["id"]; ?>">
        <div>
            <label>Nov status:</label>
            <select name="status">
                <option value="reported" <?php echo $repair["status"] == "reported" ? "selected" : ""; ?>>Prijavljeno</option>
                <option value="in_progress" <?php echo $repair["status"] == "in_progress" ? "selected" : ""; ?>>V teku</option>
                <option value="completed" <?php echo $repair["status"] == "completed" ? "selected" : ""; ?>>Zaključeno</option>
                <option value="cancelled" <?php echo $repair["status"] == "cancelled" ? "selected" : ""; ?>>Preklicano</option>
            </select>
        </div>
        <?php if (isAdmin()): ?>
        <div>
            <label>Dodeli tehniku:</label>
            <select name="assigned_to">
                <option value="">-- Brez dodelitve --</option>
                <?php
                $sql = "SELECT id, username FROM users WHERE role = 'technician' ORDER BY username";
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='" . $row["id"] . "'" . ($repair["assigned_to"] == $row["id"] ? " selected" : "") . ">" . htmlspecialchars($row["username"]) . "</option>";
                    }
                }
                ?>
            </select>
        </div>
        <?php endif; ?>
        <div>
            <label>Opombe (neobvezno):</label>
            <textarea name="notes"></textarea>
        </div>
        <div>
            <input type="submit" value="Posodobi status">
        </div>
    </form>
</div>
<?php endif; ?>

<div class="comments-section">
    <h3>Komentarji</h3>
    
    <?php if (!empty($comments)): ?>
        <?php foreach ($comments as $comment): ?>
            <div class="comment">
                <p><strong><?php echo htmlspecialchars($comment["username"]); ?></strong> (<?php echo $comment["role"]; ?>) - <?php echo $comment["created_at"]; ?></p>
                <p><?php echo nl2br(htmlspecialchars($comment["comment"])); ?></p>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Ni komentarjev.</p>
    <?php endif; ?>
    
    <h4>Dodaj komentar</h4>
    <form action="add_comment.php" method="post">
        <input type="hidden" name="repair_id" value="<?php echo $repair["id"]; ?>">
        <div>
            <textarea name="comment" required></textarea>
        </div>
        <div>
            <input type="submit" value="Dodaj komentar">
        </div>
    </form>
</div>

<a href="dashboard.php">Nazaj na nadzorno ploščo</a>
<?php include_once 'includes/footer.php'; ?>