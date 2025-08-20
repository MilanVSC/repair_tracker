<?php
include 'includes/config.php';
include 'includes/auth.php';
requireAdmin();

$name = $type = $serial = $purchase_date = $warranty = $notes = "";
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
        $sql = "INSERT INTO devices (name, type, serial_number, purchase_date, warranty_expiry, notes, created_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssssssi", $param_name, $param_type, $param_serial, $param_pdate, $param_warranty, $param_notes, $param_creator);

            $param_name = $name;
            $param_type = $type;
            $param_serial = !empty($serial) ? $serial : NULL;
            $param_pdate = !empty($purchase_date) ? $purchase_date : NULL;
            $param_warranty = !empty($warranty) ? $warranty : NULL;
            $param_notes = !empty($notes) ? $notes : NULL;
            $param_creator = $_SESSION["user_id"];

            if ($stmt->execute()) {
                header("location: admin_devices.php");
            } else {
                echo "Napaka pri dodajanju naprave. Poskusite znova.";
            }
            $stmt->close();
        }
    }
}
?>

<?php include 'includes/header.php'; ?>
    <h2>Dodaj novo napravo</h2>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <div>
            <label>Ime naprave *:</label>
            <input type="text" name="name" value="<?php echo $name; ?>">
            <span><?php echo $name_err; ?></span>
        </div>
        <div>
            <label>Tip naprave *:</label>
            <input type="text" name="type" value="<?php echo $type; ?>">
            <span><?php echo $type_err; ?></span>
        </div>
        <div>
            <label>Serijska številka:</label>
            <input type="text" name="serial_number" value="<?php echo $serial; ?>">
        </div>
        <div>
            <label>Datum nakupa:</label>
            <input type="date" name="purchase_date" value="<?php echo $purchase_date; ?>">
        </div>
        <div>
            <label>Datum izteka garancije:</label>
            <input type="date" name="warranty_expiry" value="<?php echo $warranty; ?>">
        </div>
        <div>
            <label>Opombe:</label>
            <textarea name="notes"><?php echo $notes; ?></textarea>
        </div>
        <div>
            <input type="submit" value="Dodaj napravo">
            <a href="admin_devices.php">Prekliči</a>
        </div>
        <p>* Obvezno polje</p>
    </form>
<?php include 'includes/footer.php'; ?>