<?php
include_once 'includes/config.php';
include_once 'includes/auth.php';
requireLogin();

$title = $description = $device_id = $priority = "";
$title_err = $description_err = $device_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validacija naslova
    if (empty(trim($_POST["title"]))) {
        $title_err = "Vnesite naslov popravila.";
    } else {
        $title = trim($_POST["title"]);
    }

    // Validacija opisa
    if (empty(trim($_POST["description"]))) {
        $description_err = "Vnesite opis težave.";
    } else {
        $description = trim($_POST["description"]);
    }

    // Validacija naprave
    if (empty($_POST["device_id"])) {
        $device_err = "Izberite napravo.";
    } else {
        $device_id = trim($_POST["device_id"]);
    }

    // Prioriteta
    $priority = isset($_POST["priority"]) ? trim($_POST["priority"]) : "medium";

    if (empty($title_err) && empty($description_err) && empty($device_err)) {
        $sql = "INSERT INTO repairs (device_id, reported_by, title, description, priority) VALUES (?, ?, ?, ?, ?)";
        
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("iisss", $param_device, $param_reported, $param_title, $param_desc, $param_priority);
            
            $param_device = $device_id;
            $param_reported = $_SESSION["user_id"];
            $param_title = $title;
            $param_desc = $description;
            $param_priority = $priority;
            
            if ($stmt->execute()) {
                header("location: dashboard.php");
            } else {
                echo "Napaka pri dodajanju popravila. Poskusite znova.";
            }
            $stmt->close();
        }
    }
}
?>

<?php include 'includes/header.php'; ?>
<h2>Dodaj novo popravilo</h2>
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    <div>
        <label>Naprava:</label>
        <select name="device_id">
            <option value="">-- Izberite napravo --</option>
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
        <span><?php echo $device_err; ?></span>
    </div>
    <div>
        <label>Naslov:</label>
        <input type="text" name="title" value="<?php echo $title; ?>">
        <span><?php echo $title_err; ?></span>
    </div>
    <div>
        <label>Opis težave:</label>
        <textarea name="description"><?php echo $description; ?></textarea>
        <span><?php echo $description_err; ?></span>
    </div>
    <div>
        <label>Prioriteta:</label>
        <select name="priority">
            <option value="low" <?php echo $priority == "low" ? "selected" : ""; ?>>Nizka</option>
            <option value="medium" <?php echo $priority == "medium" || empty($priority) ? "selected" : ""; ?>>Srednja</option>
            <option value="high" <?php echo $priority == "high" ? "selected" : ""; ?>>Visoka</option>
            <option value="critical" <?php echo $priority == "critical" ? "selected" : ""; ?>>Kritična</option>
        </select>
    </div>
    <div>
        <input type="submit" value="Dodaj popravilo">
        <a href="dashboard.php">Prekliči</a>
    </div>
</form>
<?php include 'includes/footer.php'; ?>