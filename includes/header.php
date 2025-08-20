<?php
include_once 'includes/auth.php';
?>
<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem za sledenje popravilom</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<header>
    <h1>Sistem za sledenje popravilom</h1>
    <nav>
        <?php if (isLoggedIn()): ?>
            <a href="dashboard.php">Nadzorna plošča</a>
            <a href="all_repairs.php">Vsa popravila</a>
            <a href="add_repair.php">Novo popravilo</a>
            <?php if (isAdmin()): ?>
                <a href="admin_devices.php">Naprave</a>
                <a href="admin_users.php">Uporabniki</a>
                <a href="admin_repairs.php">Upravljanje popravil</a>
                <a href="admin_comments.php">Komentarji</a>
            <?php endif; ?>
            <a href="profile.php">Moj profil</a>
            <a href="logout.php">Odjava</a>
        <?php else: ?>
            <a href="login.php">Prijava</a>
            <a href="register.php">Registracija</a>
        <?php endif; ?>
    </nav>
</header>
<main>