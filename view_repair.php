<?php
include_once 'includes/config.php';
include_once 'includes/auth.php';
requireLogin();

if (!isset($_GET["id"]) || empty(trim($_GET["id"]))) {
    header("location: all_repairs.php");
    exit;
}

$repair_id = trim($_GET["id"]);
$repair = $comments = [];

// Pridobi podatke o popravilu - BREZ preverjanja dostopa
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
        header("location: all_repairs.php");
        exit;
    }
    $stmt->close();
}

// Pridobi komentarje
$sql = "SELECT c.*, u.username, u.role, u.avatar
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

    <div style="margin-bottom: 20px;">
        <a href="all_repairs.php" class="button">Nazaj na vsa popravila</a>
        <?php if (isAdmin() || $repair["reported_by"] == $_SESSION["user_id"]): ?>
            <a href="admin_edit_repair.php?id=<?php echo $repair["id"]; ?>" class="button">Uredi popravilo</a>
        <?php endif; ?>
        <a href="add_repair_images.php?id=<?php echo $repair["id"]; ?>" class="button">Dodaj slike</a>
    </div>

    <div class="repair-details">
        <h3><?php echo htmlspecialchars($repair["title"]); ?></h3>

        <div class="detail-grid">
            <div class="detail-item">
                <strong>Naprava:</strong> <?php echo htmlspecialchars($repair["device_name"]); ?>
            </div>
            <div class="detail-item">
                <strong>Status:</strong>
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
            </div>
            <div class="detail-item">
                <strong>Prioriteta:</strong>
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
            </div>
            <div class="detail-item">
                <strong>Prijavil:</strong> <?php echo htmlspecialchars($repair["reported_by_name"]); ?>
            </div>
            <?php if ($repair["assigned_to_name"]): ?>
                <div class="detail-item">
                    <strong>Dodeljeno:</strong> <?php echo htmlspecialchars($repair["assigned_to_name"]); ?>
                </div>
            <?php endif; ?>
            <div class="detail-item">
                <strong>Datum prijave:</strong> <?php echo date('d.m.Y H:i', strtotime($repair["reported_date"])); ?>
            </div>
            <?php if ($repair["completed_date"]): ?>
                <div class="detail-item">
                    <strong>Datum zaključka:</strong> <?php echo date('d.m.Y H:i', strtotime($repair["completed_date"])); ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="description">
            <strong>Opis:</strong>
            <p><?php echo nl2br(htmlspecialchars($repair["description"])); ?></p>
        </div>
    </div>

    <!-- Galerija slik -->
    <div class="repair-images">
        <h3>Slike popravila</h3>

        <?php if (!empty($images)): ?>
            <div class="image-gallery">
                <?php foreach ($images as $image): ?>
                    <div class="image-item">
                        <img src="<?php echo $image['image_path']; ?>" alt="<?php echo htmlspecialchars($image['image_name']); ?>"
                             onclick="openModal('<?php echo $image['image_path']; ?>')">
                        <p>Dodal: <?php echo htmlspecialchars($image['username']); ?><br>
                            <?php echo date('d.m.Y H:i', strtotime($image['uploaded_at'])); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>Ni naloženih slik.</p>
        <?php endif; ?>

        <a href="add_repair_images.php?id=<?php echo $repair['id']; ?>" class="button">Dodaj slike</a>
    </div>

<?php if (isAdmin() || isTechnician() || $repair["reported_by"] == $_SESSION["user_id"]): ?>
    <div class="repair-actions">
        <h3>Upravljanje popravila</h3>
        <form action="update_status.php" method="post">
            <input type="hidden" name="repair_id" value="<?php echo $repair["id"]; ?>">
            <div class="form-group">
                <label>Nov status:</label>
                <select name="status">
                    <option value="reported" <?php echo $repair["status"] == "reported" ? "selected" : ""; ?>>Prijavljeno</option>
                    <option value="in_progress" <?php echo $repair["status"] == "in_progress" ? "selected" : ""; ?>>V teku</option>
                    <option value="completed" <?php echo $repair["status"] == "completed" ? "selected" : ""; ?>>Zaključeno</option>
                    <option value="cancelled" <?php echo $repair["status"] == "cancelled" ? "selected" : ""; ?>>Preklicano</option>
                </select>
            </div>
            <?php if (isAdmin()): ?>
                <div class="form-group">
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
            <div class="form-group">
                <label>Opombe (neobvezno):</label>
                <textarea name="notes" placeholder="Dodatne opombe ob spremembi statusa..."></textarea>
            </div>
            <div class="form-group">
                <input type="submit" value="Posodobi status" class="button">
            </div>
        </form>
    </div>
<?php endif; ?>

    <div class="comments-section">
        <h3>Komentarji</h3>

        <?php if (!empty($comments)): ?>
            <?php foreach ($comments as $comment): ?>
                <div class="comment">
                    <div class="comment-header">
                        <?php if ($comment["avatar"] && file_exists($comment["avatar"])): ?>
                            <img src="<?php echo $comment['avatar']; ?>" alt="Avatar" class="comment-avatar">
                        <?php else: ?>
                            <div class="comment-avatar-default"><?php echo strtoupper(substr($comment["username"], 0, 1)); ?></div>
                        <?php endif; ?>
                        <div class="comment-info">
                            <strong><?php echo htmlspecialchars($comment["username"]); ?></strong>
                            <span>(<?php echo $comment["role"]; ?>)</span>
                            <span class="comment-date"><?php echo date('d.m.Y H:i', strtotime($comment["created_at"])); ?></span>
                        </div>
                    </div>
                    <div class="comment-content">
                        <?php echo nl2br(htmlspecialchars($comment["comment"])); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Ni komentarjev.</p>
        <?php endif; ?>

        <h4>Dodaj komentar</h4>
        <form action="add_comment.php" method="post">
            <input type="hidden" name="repair_id" value="<?php echo $repair["id"]; ?>">
            <div class="form-group">
                <textarea name="comment" required placeholder="Vnesite vaš komentar..." rows="4"></textarea>
            </div>
            <div class="form-group">
                <input type="submit" value="Dodaj komentar" class="button">
            </div>
        </form>
    </div>

    <!-- Modal za slike -->
    <div id="imageModal" class="modal">
        <span class="close">&times;</span>
        <img class="modal-content" id="modalImage">
    </div>

    <script>
        function openModal(src) {
            document.getElementById('modalImage').src = src;
            document.getElementById('imageModal').style.display = 'block';
        }

        document.querySelector('.close').addEventListener('click', function() {
            document.getElementById('imageModal').style.display = 'none';
        });

        window.addEventListener('click', function(event) {
            if (event.target == document.getElementById('imageModal')) {
                document.getElementById('imageModal').style.display = 'none';
            }
        });
    </script>

<?php include_once 'includes/footer.php'; ?>