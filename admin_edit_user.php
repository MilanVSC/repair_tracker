<?php
include 'includes/config.php';
include 'includes/auth.php';
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
            $sql = "SELECT id FROM users WHERE username = ?";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("s", $new_username);
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
            $sql = "SELECT id FROM users WHERE email = ?";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("s", $new_email);
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

<?php include 'includes/header.php'; ?>
    <h2>Uredi uporabnika</h2>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?id=<?php echo $user_id; ?>" method="post">
        <div>
            <label>Uporabniško ime *:</label>
            <input type="text" name="username" value="<?php echo $username; ?>">
            <span><?php echo $username_err; ?></span>
        </div>
        <div>
            <label>Email *:</label>
            <input type="email" name="email" value="<?php echo $email; ?>">
            <span><?php echo $email_err; ?></span>
        </div>
        <div>
            <label>Polno ime:</label>
            <input type="text" name="full_name" value="<?php echo $full_name; ?>">
        </div>
        <div>
            <label>Vloga *:</label>
            <select name="role">
                <option value="user" <?php echo $role == "user" ? "selected" : ""; ?>>Uporabnik</option>
                <option value="technician" <?php echo $role == "technician" ? "selected" : ""; ?>>Tehnik</option>
                <option value="admin" <?php echo $role == "admin" ? "selected" : ""; ?>>Administrator</option>
            </select>
        </div>
        <div>
            <input type="submit" value="Shrani spremembe">
            <a href="admin_users.php">Prekliči</a>
        </div>
        <p>* Obvezno polje</p>
    </form>
<?php include 'includes/footer.php'; ?>