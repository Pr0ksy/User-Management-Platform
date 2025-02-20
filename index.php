<?php
session_start();
if (isset($_SESSION['username'])) {
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="author" content="Jovan Prodanić">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login & Registracija</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body>
    <div class="container">
        <h2>Registracija</h2>
        <form method="POST" action="auth.php">
            <input type="text" name="full_name" placeholder="Puno ime" required>
            <input type="text" name="username" placeholder="Korisničko ime" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Lozinka" required>
            <button type="submit" name="register">Registruj se</button>
            <p>Već imate nalog? <a href="login.php">Prijavite se ovde</a></p>
        </form>
    </div>
</body>
</html>