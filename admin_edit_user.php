<?php
include_once 'includes/config.php';
include_once 'includes/auth.php';
requireAdmin();

if (!isset($_GET["id"]) || empty(trim($_GET["id"]))) {
    header("location: admin_users.php");
    exit;
}

$user_id = trim($_GET["id"]);
$user = [];

// Pridobi podatke o uporabniku
$sql = "SELECT id, username, email, full_name, role FROM users WHERE id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
    } else {
        header("location: admin_users.php");
        exit;
    }
    $stmt->close();
} else {
    header("location: admin_users.php");
    exit;
}

$username = $user["username"];
$email = $user["email"];
$full_name = $user["full_name"];
$role = $user["role"];
$username_err = $email_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty(trim($_POST["username"]))) {
        $username_err = "Vnesite uporabniško ime.";
    } else {
        $new_username = trim($_POST["username"]);
        if ($new_username != $username) {
            $sql = "SELECT id FROM users WHERE username = ? AND id != ?";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("si", $new_username, $user_id);
                $stmt->execute();
                $stmt->store_result();
                if ($stmt->num_rows == 1) {
                    $username_err = "To uporabniško ime je že zasedeno.";
                } else {
                    $username = $new_username;
                }
                $stmt->close();
            }
        }
    }

    if (empty(trim($_POST["email"]))) {
        $email_err = "Vnesite email naslov.";
    } else {
        $new_email = trim($_POST["email"]);
        if ($new_email != $email) {
            $sql = "SELECT id FROM users WHERE email = ? AND id != ?";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("si", $new_email, $user_id);
                $stmt->execute();
                $stmt->store_result();
                if ($stmt->num_rows == 1) {
                    $email_err = "Ta email naslov je že registriran.";
                } else {
                    $email = $new_email;
                }
                $stmt->close();
            }
        }
    }

    $full_name = trim($_POST["full_name"]);
    $role = trim($_POST["role"]);

    if (empty($username_err) && empty($email_err)) {
        $sql = "UPDATE users SET username=?, email=?, full_name=?, role=? WHERE id=?";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssssi", $username, $email, $full_name, $role, $user_id);

            if ($stmt->execute()) {
                header("location: admin_users.php");
            } else {
                echo "Napaka pri posodabljanju uporabnika. Poskusite znova.";
            }
            $stmt->close();
        }
    }
}
?>

<?php include_once 'includes/header.php'; ?>
    <h2>Uredi uporabnika</h2>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?id=<?php echo $user_id; ?>" method="post">
        <div class="form-group">
            <label>Uporabniško ime *:</label>
            <input type="text" name="username" value="<?php echo htmlspecialchars($username); ?>">
            <span class="error"><?php echo $username_err; ?></span>
        </div>
        <div class="form-group">
            <label>Email *:</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>">
            <span class="error"><?php echo $email_err; ?></span>
        </div>
        <div class="form-group">
            <label>Polno ime:</label>
            <input type="text" name="full_name" value="<?php echo htmlspecialchars($full_name); ?>">
        </div>
        <div class="form-group">
            <label>Vloga *:</label>
            <select name="role">
                <option value="user" <?php echo $role == "user" ? "selected" : ""; ?>>Uporabnik</option>
                <option value="technician" <?php echo $role == "technician" ? "selected" : ""; ?>>Tehnik</option>
                <option value="admin" <?php echo $role == "admin" ? "selected" : ""; ?>>Administrator</option>
            </select>
        </div>
        <div class="form-group">
            <input type="submit" value="Shrani spremembe" class="button">
            <a href="admin_users.php" class="button button-secondary">Prekliči</a>
        </div>
        <p>* Obvezno polje</p>
    </form>
<?php include_once 'includes/footer.php'; ?>