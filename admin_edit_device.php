<?php
include_once 'includes/config.php';
include_once 'includes/auth.php';
requireAdmin();

if (!isset($_GET["id"]) || empty(trim($_GET["id"]))) {
    header("location: admin_devices.php");
    exit;
}

$device_id = trim($_GET["id"]);
$device = [];

// Pridobi podatke o napravi
$sql = "SELECT * FROM devices WHERE id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $device_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $device = $result->fetch_assoc();
    } else {
        header("location: admin_devices.php");
        exit;
    }
    $stmt->close();
} else {
    header("location: admin_devices.php");
    exit;
}

$name = $device["name"];
$type = $device["type"];
$serial = $device["serial_number"];
$purchase_date = $device["purchase_date"];
$warranty = $device["warranty_expiry"];
$notes = $device["notes"];
$name_err = $type_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty(trim($_POST["name"]))) {
        $name_err = "Vnesite ime naprave.";
    } else {
        $name = trim($_POST["name"]);
    }

    if (empty(trim($_POST["type"]))) {
        $type_err = "Vnesite tip naprave.";
    } else {
        $type = trim($_POST["type"]);
    }

    $serial = trim($_POST["serial_number"]);
    $purchase_date = trim($_POST["purchase_date"]);
    $warranty = trim($_POST["warranty_expiry"]);
    $notes = trim($_POST["notes"]);

    if (empty($name_err) && empty($type_err)) {
        $sql = "UPDATE devices SET name=?, type=?, serial_number=?, purchase_date=?, warranty_expiry=?, notes=? WHERE id=?";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssssssi", $name, $type, $serial, $purchase_date, $warranty, $notes, $device_id);

            if ($stmt->execute()) {
                header("location: admin_devices.php");
            } else {
                echo "Napaka pri posodabljanju naprave. Poskusite znova.";
            }
            $stmt->close();
        }
    }
}
?>

<?php include_once 'includes/header.php'; ?>
    <h2>Uredi napravo</h2>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?id=<?php echo $device_id; ?>" method="post">
        <div class="form-group">
            <label>Ime naprave *:</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($name); ?>">
            <span class="error"><?php echo $name_err; ?></span>
        </div>
        <div class="form-group">
            <label>Tip naprave *:</label>
            <input type="text" name="type" value="<?php echo htmlspecialchars($type); ?>">
            <span class="error"><?php echo $type_err; ?></span>
        </div>
        <div class="form-group">
            <label>Serijska številka:</label>
            <input type="text" name="serial_number" value="<?php echo htmlspecialchars($serial); ?>">
        </div>
        <div class="form-group">
            <label>Datum nakupa:</label>
            <input type="date" name="purchase_date" value="<?php echo $purchase_date; ?>">
        </div>
        <div class="form-group">
            <label>Datum izteka garancije:</label>
            <input type="date" name="warranty_expiry" value="<?php echo $warranty; ?>">
        </div>
        <div class="form-group">
            <label>Opombe:</label>
            <textarea name="notes"><?php echo htmlspecialchars($notes); ?></textarea>
        </div>
        <div class="form-group">
            <input type="submit" value="Shrani spremembe" class="button">
            <a href="admin_devices.php" class="button button-secondary">Prekliči</a>
        </div>
        <p>* Obvezno polje</p>
    </form>
<?php include_once 'includes/footer.php'; ?>