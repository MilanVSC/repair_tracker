<?php
include_once 'includes/config.php';
include_once 'includes/auth.php';

if (isLoggedIn()) {
    header("location: dashboard.php");
    exit;
}

$username = $password = $confirm_password = $email = $full_name = "";
$username_err = $password_err = $confirm_password_err = $email_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validacija uporabniškega imena
    if (empty(trim($_POST["username"]))) {
        $username_err = "Vnesite uporabniško ime.";
    } else {
        $sql = "SELECT id FROM users WHERE username = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $param_username);
            $param_username = trim($_POST["username"]);
            if ($stmt->execute()) {
                $stmt->store_result();
                if ($stmt->num_rows == 1) {
                    $username_err = "To uporabniško ime je že zasedeno.";
                } else {
                    $username = trim($_POST["username"]);
                }
            }
            $stmt->close();
        }
    }

    // Validacija emaila
    if (empty(trim($_POST["email"]))) {
        $email_err = "Vnesite email naslov.";
    } else {
        $sql = "SELECT id FROM users WHERE email = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $param_email);
            $param_email = trim($_POST["email"]);
            if ($stmt->execute()) {
                $stmt->store_result();
                if ($stmt->num_rows == 1) {
                    $email_err = "Ta email naslov je že registriran.";
                } else {
                    $email = trim($_POST["email"]);
                }
            }
            $stmt->close();
        }
    }

    // Validacija gesla
    if (empty(trim($_POST["password"]))) {
        $password_err = "Vnesite geslo.";
    } elseif (strlen(trim($_POST["password"])) < 6) {
        $password_err = "Geslo mora vsebovati vsaj 6 znakov.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Potrditev gesla
    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Potrdite geslo.";
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($password_err) && ($password != $confirm_password)) {
            $confirm_password_err = "Gesli se ne ujemata.";
        }
    }

    // Polno ime
    $full_name = trim($_POST["full_name"]);

    // Preveri napake pred vstavljanjem v bazo
    if (empty($username_err) && empty($password_err) && empty($confirm_password_err) && empty($email_err)) {
        $sql = "INSERT INTO users (username, password, email, full_name) VALUES (?, ?, ?, ?)";
         
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssss", $param_username, $param_password, $param_email, $param_full_name);
            
            $param_username = $username;
            $param_password = password_hash($password, PASSWORD_DEFAULT);
            $param_email = $email;
            $param_full_name = $full_name;
            
            if ($stmt->execute()) {
                header("location: login.php");
            } else {
                echo "Oops! Prišlo je do napake. Poskusite znova kasneje.";
            }
            $stmt->close();
        }
    }
    $conn->close();
}
?>

<?php include_once 'includes/header.php'; ?>
<h2>Registracija</h2>
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    <div>
        <label>Uporabniško ime:</label>
        <input type="text" name="username" value="<?php echo $username; ?>">
        <span><?php echo $username_err; ?></span>
    </div>
    <div>
        <label>Email:</label>
        <input type="email" name="email" value="<?php echo $email; ?>">
        <span><?php echo $email_err; ?></span>
    </div>
    <div>
        <label>Geslo:</label>
        <input type="password" name="password">
        <span><?php echo $password_err; ?></span>
    </div>
    <div>
        <label>Potrdi geslo:</label>
        <input type="password" name="confirm_password">
        <span><?php echo $confirm_password_err; ?></span>
    </div>
    <div>
        <label>Polno ime:</label>
        <input type="text" name="full_name" value="<?php echo $full_name; ?>">
    </div>
    <div>
        <input type="submit" value="Registracija">
    </div>
    <p>Že imate račun? <a href="login.php">Prijavite se</a>.</p>
</form>
<?php include_once 'includes/footer.php'; ?>