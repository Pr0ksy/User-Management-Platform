<?php
session_start();
include 'db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $conn->real_escape_string($_POST['email']);

    // Provera da li email postoji u bazi
    $sql = "SELECT * FROM users WHERE email=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Generisanje jedinstvenog tokena
        $token = bin2hex(random_bytes(50));
        $expires = time() + 3600; // Token važi 1 sat

        // Upisivanje tokena u bazu
        $update = "UPDATE users SET reset_token=?, reset_expires=? WHERE email=?";
        $stmt = $conn->prepare($update);
        $stmt->bind_param("sis", $token, $expires, $email);
        $stmt->execute();

        // Slanje emaila (OVDE UBACI SMTP KONFIGURACIJU)
        $reset_link = "http://localhost/usermanagement/reset_password.php?token=" . $token;
        $to = $email;
        $subject = "Resetovanje lozinke";
        $message = "Kliknite na sledeći link kako biste resetovali lozinku: " . $reset_link;
        $headers = "From: admin@yoursite.com\r\n";

        if (mail($to, $subject, $message, $headers)) {
            $success = "Email za resetovanje lozinke je poslat!";
        } else {
            $error = "Greška pri slanju emaila!";
        }
    } else {
        $error = "Email ne postoji u bazi!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Zaboravljena lozinka</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body>
    <div class="container">
        <h2>Resetuj lozinku</h2>
        <?php if (isset($error)): ?>
            <p style="color: red;"><?php echo $error; ?></p>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <p style="color: green;"><?php echo $success; ?></p>
        <?php endif; ?>
        <form method="POST" action="">
            <input type="email" name="email" placeholder="Unesite email" required>
            <button type="submit">Pošalji link</button>
        </form>
    </div>
</body>
</html>
