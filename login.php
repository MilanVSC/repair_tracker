<?php
include_once 'includes/config.php';
include_once 'includes/auth.php';

if (isLoggedIn()) {
    header("location: dashboard.php");
    exit;
}

$username = $password = "";
$username_err = $password_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty(trim($_POST["username"]))) {
        $username_err = "Vnesite uporabniško ime.";
    } else {
        $username = trim($_POST["username"]);
    }

    if (empty(trim($_POST["password"]))) {
        $password_err = "Vnesite geslo.";
    } else {
        $password = trim($_POST["password"]);
    }

    if (empty($username_err) && empty($password_err)) {
        $sql = "SELECT id, username, password, role FROM users WHERE username = ?";
        
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $param_username);
            $param_username = $username;
            
            if ($stmt->execute()) {
                $stmt->store_result();
                
                if ($stmt->num_rows == 1) {
                    $stmt->bind_result($id, $username, $hashed_password, $role);
                    if ($stmt->fetch()) {
                        if (password_verify($password, $hashed_password)) {
                            session_start();
                            
                            $_SESSION["user_id"] = $id;
                            $_SESSION["username"] = $username;
                            $_SESSION["role"] = $role;
                            
                            header("location: dashboard.php");
                        } else {
                            $password_err = "Napačno geslo.";
                        }
                    }
                } else {
                    $username_err = "Uporabniško ime ne obstaja.";
                }
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
<h2>Prijava</h2>
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    <div>
        <label>Uporabniško ime:</label>
        <input type="text" name="username" value="<?php echo $username; ?>">
        <span><?php echo $username_err; ?></span>
    </div>
    <div>
        <label>Geslo:</label>
        <input type="password" name="password">
        <span><?php echo $password_err; ?></span>
    </div>
    <div>
        <input type="submit" value="Prijava">
    </div>
    <p>Nimate računa? <a href="register.php">Registrirajte se</a>.</p>
</form>
<?php include_once 'includes/footer.php'; ?>