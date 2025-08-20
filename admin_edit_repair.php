<?php
include_once 'includes/config.php';
include_once 'includes/auth.php';
requireAdmin();

if (!isset($_GET["id"]) || empty(trim($_GET["id"]))) {
    header("location: admin_repairs.php");
    exit;
}

$repair_id = trim($_GET["id"]);
$repair = [];

// Pridobi podatke o popravilu
$sql = "SELECT * FROM repairs WHERE id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $repair_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $repair = $result->fetch_assoc();
    } else {
        header("location: admin_repairs.php");
        exit;
    }
    $stmt->close();
} else {
    header("location: admin_repairs.php");
    exit;
}

$title = $repair["title"];
$description = $repair["description"];
$device_id = $repair["device_id"];
$assigned_to = $repair["assigned_to"];
$status = $repair["status"];
$priority = $repair["priority"];
$title_err = $description_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty(trim($_POST["title"]))) {
        $title_err = "Vnesite naslov popravila.";
    } else {
        $title = trim($_POST["title"]);
    }

    if (empty(trim($_POST["description"]))) {
        $description_err = "Vnesite opis težave.";
    } else {
        $description = trim($_POST["description"]);
    }

    $device_id = trim($_POST["device_id"]);
    $assigned_to = !empty($_POST["assigned_to"]) ? trim($_POST["assigned_to"]) : NULL;
    $status = trim($_POST["status"]);
    $priority = trim($_POST["priority"]);

    if (empty($title_err) && empty($description_err)) {
        $sql = "UPDATE repairs SET title=?, description=?, device_id=?, assigned_to=?, status=?, priority=? WHERE id=?";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssiissi", $title, $description, $device_id, $assigned_to, $status, $priority, $repair_id);

            if ($stmt->execute()) {
                header("location: admin_repairs.php");
            } else {
                echo "Napaka pri posodabljanju popravila. Poskusite znova.";
            }
            $stmt->close();
        }
    }
}
?>

<?php include_once 'includes/header.php'; ?>
    <h2>Uredi popravilo</h2>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?id=<?php echo $repair_id; ?>" method="post">
        <div class="form-group">
            <label>Naslov *:</label>
            <input type="text" name="title" value="<?php echo htmlspecialchars($title); ?>">
            <span class="error"><?php echo $title_err; ?></span>
        </div>
        <div class="form-group">
            <label>Opis težave *:</label>
            <textarea name="description"><?php echo htmlspecialchars($description); ?></textarea>
            <span class="error"><?php echo $description_err; ?></span>
        </div>
        <div class="form-group">
            <label>Naprava *:</label>
            <select name="device_id">
                <?php
                $sql = "SELECT id, name FROM devices ORDER BY name";
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='" . $row["id"] . "'" . ($device_id == $row["id"] ? " selected" : "") . ">" . htmlspecialchars($row["name"]) . "</option>";
                    }
                }
                ?>
            </select>
        </div>
        <div class="form-group">
            <label>Dodeli tehniku:</label>
            <select name="assigned_to">
                <option value="">-- Brez dodelitve --</option>
                <?php
                $sql = "SELECT id, username FROM users WHERE role = 'technician' ORDER BY username";
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $selected = ($assigned_to == $row["id"]) ? " selected" : "";
                        echo "<option value='" . $row["id"] . "'" . $selected . ">" . htmlspecialchars($row["username"]) . "</option>";
                    }
                }
                ?>
            </select>
        </div>
        <div class="form-group">
            <label>Status:</label>
            <select name="status">
                <option value="reported" <?php echo $status == "reported" ? "selected" : ""; ?>>Prijavljeno</option>
                <option value="in_progress" <?php echo $status == "in_progress" ? "selected" : ""; ?>>V teku</option>
                <option value="completed" <?php echo $status == "completed" ? "selected" : ""; ?>>Zaključeno</option>
                <option value="cancelled" <?php echo $status == "cancelled" ? "selected" : ""; ?>>Preklicano</option>
            </select>
        </div>
        <div class="form-group">
            <label>Prioriteta:</label>
            <select name="priority">
                <option value="low" <?php echo $priority == "low" ? "selected" : ""; ?>>Nizka</option>
                <option value="medium" <?php echo $priority == "medium" ? "selected" : ""; ?>>Srednja</option>
                <option value="high" <?php echo $priority == "high" ? "selected" : ""; ?>>Visoka</option>
                <option value="critical" <?php echo $priority == "critical" ? "selected" : ""; ?>>Kritična</option>
            </select>
        </div>
        <div class="form-group">
            <input type="submit" value="Shrani spremembe" class="button">
            <a href="admin_repairs.php" class="button button-secondary">Prekliči</a>
        </div>
        <p>* Obvezno polje</p>
    </form>
<?php include_once 'includes/footer.php'; ?>