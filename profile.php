<?php
include_once 'includes/config.php';
include_once 'includes/auth.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$username = $email = $full_name = $current_avatar = "";
$username_err = $email_err = $avatar_err = "";

// Pridobi trenutne podatke
$sql = "SELECT username, email, full_name, avatar FROM users WHERE id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($username, $email, $full_name, $current_avatar);
    $stmt->fetch();
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validacija uporabniškega imena
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

    // Validacija emaila
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

    // Obdelava avatarja
    if (!empty($_FILES["avatar"]["name"])) {
        $avatar_name = basename($_FILES["avatar"]["name"]);
        $target_file = AVATAR_DIR . uniqid() . "_" . $avatar_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        // Preveri velikost (max 2MB)
        if ($_FILES["avatar"]["size"] > 2000000) {
            $avatar_err = "Slika je prevelika (max 2MB).";
        }
        // Preveri format
        elseif (!in_array($imageFileType, ["jpg", "jpeg", "png", "gif"])) {
            $avatar_err = "Dovoljeni so le JPG, JPEG, PNG in GIF formati.";
        }
        // Poskusi upload
        elseif (move_uploaded_file($_FILES["avatar"]["tmp_name"], $target_file)) {
            // Izbriši star avatar, če obstaja
            if ($current_avatar && file_exists($current_avatar)) {
                unlink($current_avatar);
            }
            $current_avatar = $target_file;
        } else {
            $avatar_err = "Napaka pri nalaganju slike.";
        }
    }

    if (empty($username_err) && empty($email_err) && empty($avatar_err)) {
        $sql = "UPDATE users SET username=?, email=?, full_name=?, avatar=? WHERE id=?";
        
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssssi", $username, $email, $full_name, $current_avatar, $user_id);
            
            if ($stmt->execute()) {
                $_SESSION["username"] = $username;
                $success = "Profil uspešno posodobljen.";
            } else {
                $error = "Napaka pri posodabljanju profila.";
            }
            $stmt->close();
        }
    }
}
?>

<?php include_once 'includes/header.php'; ?>
<h2>Uredi profil</h2>

<?php if (isset($success)): ?>
    <div class="success"><?php echo $success; ?></div>
<?php endif; ?>

<?php if (isset($error)): ?>
    <div class="error"><?php echo $error; ?></div>
<?php endif; ?>

<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
    <div>
        <label>Trenutni avatar:</label>
        <?php if ($current_avatar && file_exists($current_avatar)): ?>
            <img src="<?php echo $current_avatar; ?>" alt="Avatar" style="width: 100px; height: 100px; border-radius: 50%;">
        <?php else: ?>
            <p>Brez avatarja</p>
        <?php endif; ?>
    </div>
    
    <div>
        <label>Nov avatar:</label>
        <input type="file" name="avatar" accept="image/*">
        <span><?php echo $avatar_err; ?></span>
    </div>
    
    <div>
        <label>Uporabniško ime *:</label>
        <input type="text" name="username" value="<?php echo htmlspecialchars($username); ?>">
        <span><?php echo $username_err; ?></span>
    </div>
    
    <div>
        <label>Email *:</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>">
        <span><?php echo $email_err; ?></span>
    </div>
    
    <div>
        <label>Polno ime:</label>
        <input type="text" name="full_name" value="<?php echo htmlspecialchars($full_name); ?>">
    </div>
    
    <div>
        <input type="submit" value="Shrani spremembe">
        <a href="dashboard.php">Prekliči</a>
    </div>
</form>

<?php include_once 'includes/footer.php'; ?>