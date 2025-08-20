<?php
include_once 'includes/config.php';
include_once 'includes/auth.php';
requireAdmin();

if (!isset($_GET["id"]) || empty(trim($_GET["id"]))) {
    header("location: admin_repairs.php");
    exit;
}
// ... koda ...

if (empty($title_err) && empty($description_err)) {
    $sql = "UPDATE repairs SET title=?, description=?, device_id=?, assigned_to=?, status=?, priority=? WHERE id=?";
    
    if ($stmt = $conn->prepare($sql)) {
        // ... koda ...
        
        if ($stmt->execute()) {
            header("location: admin_repairs.php");
        } else {
            echo "Napaka pri posodabljanju popravila. Poskusite znova.";
        }
        $stmt->close();
    }
}
?>

<?php include_once 'includes/header.php'; ?>
<!-- ... obrazec ... -->
    <div>
        <input type="submit" value="Shrani spremembe">
        <a href="admin_repairs.php">Prekliči</a>
    </div>
</form>
<?php include_once 'includes/footer.php'; ?>