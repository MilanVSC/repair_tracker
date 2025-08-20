<?php
include_once 'includes/config.php';
include_once 'includes/auth.php';
requireLogin();

if (!isset($_GET["id"]) || empty(trim($_GET["id"]))) {
    header("location: dashboard.php");
    exit;
}

$repair_id = trim($_GET["id"]);
$error = $success = "";

// Preveri če popravilo obstaja in ima uporabnik dostop
$sql = "SELECT id FROM repairs WHERE id = ? AND (reported_by = ? OR assigned_to = ?)";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("iii", $repair_id, $_SESSION["user_id"], $_SESSION["user_id"]);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows == 0 && !isAdmin()) {
        header("location: dashboard.php");
        exit;
    }
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_FILES["images"]["name"][0])) {
    foreach ($_FILES["images"]["tmp_name"] as $key => $tmp_name) {
        if ($_FILES["images"]["error"][$key] === UPLOAD_ERR_OK) {
            $image_name = basename($_FILES["images"]["name"][$key]);
            $target_file = REPAIR_IMAGES_DIR . uniqid() . "_" . $image_name;
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            
            // Preveri velikost (max 5MB)
            if ($_FILES["images"]["size"][$key] > 5000000) {
                $error = "Nekatere slike so prevelike (max 5MB).";
                continue;
            }
            
            // Preveri format
            if (!in_array($imageFileType, ["jpg", "jpeg", "png", "gif"])) {
                $error = "Dovoljeni so le JPG, JPEG, PNG in GIF formati.";
                continue;
            }
            
            // Poskusi upload
            if (move_uploaded_file($tmp_name, $target_file)) {
                $sql = "INSERT INTO repair_images (repair_id, user_id, image_name, image_path) VALUES (?, ?, ?, ?)";
                if ($stmt = $conn->prepare($sql)) {
                    $stmt->bind_param("iiss", $repair_id, $_SESSION["user_id"], $image_name, $target_file);
                    $stmt->execute();
                    $stmt->close();
                    $success = "Slike uspešno naložene.";
                }
            } else {
                $error = "Napaka pri nalaganju nekaterih slik.";
            }
        }
    }
}
?>

<?php include_once 'includes/header.php'; ?>
<h2>Dodaj slike k popravilu #<?php echo $repair_id; ?></h2>

<?php if ($success): ?>
    <div class="success"><?php echo $success; ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="error"><?php echo $error; ?></div>
<?php endif; ?>

<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?id=<?php echo $repair_id; ?>" method="post" enctype="multipart/form-data">
    <div>
        <label>Izberi slike (max 5MB na sliko):</label>
        <input type="file" name="images[]" multiple accept="image/*">
    </div>
    
    <div>
        <input type="submit" value="Naloži slike">
        <a href="view_repair.php?id=<?php echo $repair_id; ?>">Nazaj na popravilo</a>
    </div>
</form>

<?php include_once 'includes/footer.php'; ?>