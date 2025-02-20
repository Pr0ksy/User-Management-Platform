<?php
session_start();
include 'db_config.php';

$error = ""; // Promenljiva za prikaz grešaka
$max_attempts = 5; // Maksimalan broj neuspelih pokušaja
$block_time = 600; // Vreme blokade u sekundama (10 minuta)

if (isset($_SESSION['username'])) {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $conn->real_escape_string($_POST['username']);
    $pass = $_POST['password'];
    $ip_address = $_SERVER['REMOTE_ADDR'];

    // Proveri koliko neuspelih pokušaja ima korisnik u poslednjih 10 minuta
    $stmt = $conn->prepare("SELECT COUNT(*) FROM user_logs WHERE username=? AND success=0 AND login_time > NOW() - INTERVAL ? SECOND");
    $stmt->bind_param("si", $user, $block_time);
    $stmt->execute();
    $stmt->bind_result($failed_attempts);
    $stmt->fetch();
    $stmt->close();

    if ($failed_attempts >= $max_attempts) {
        $error = "Vaš nalog je privremeno blokiran zbog previše neuspelih pokušaja. Pokušajte ponovo kasnije.";
    } else {
        // Proveri da li korisnik postoji
        $sql = "SELECT * FROM users WHERE username=? OR email=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $user, $user);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (password_verify($pass, $row['password'])) {
                $_SESSION['username'] = $row['username'];
                $_SESSION['full_name'] = $row['full_name'];
                $_SESSION['role'] = $row['role'];

                // Uspešan login – resetuj pokušaje
                $stmt = $conn->prepare("INSERT INTO user_logs (username, ip_address, success) VALUES (?, ?, 1)");
                $stmt->bind_param("ss", $user, $ip_address);
                $stmt->execute();
                
                header("Location: dashboard.php");
                exit();
            } else {
                // Neuspešan pokušaj – zabeleži u user_logs
                $stmt = $conn->prepare("INSERT INTO user_logs (username, ip_address, success) VALUES (?, ?, 0)");
                $stmt->bind_param("ss", $user, $ip_address);
                $stmt->execute();
                
                $error = "Pogrešna lozinka!";
            }
        } else {
            $error = "Korisnik ne postoji!";
        }
    }
}

$message = "";
if (isset($_GET['message']) && $_GET['message'] == 'success') {
    $message = "<p style='color: green; text-align: center;'>Uspešno ste se registrovali! Sada se možete prijaviti.</p>";
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body>
    <div class="container">
        <h2>Prijava</h2>
        <?php if ($error): ?>
            <p style="color: red;"> <?php echo $error; ?> </p>
        <?php endif; ?>
        <?php echo $message; ?>
        <?php if (isset($_GET['error']) && $_GET['error'] == "ban"): ?>
            <p style="color: red;">Vaš nalog je banovan!</p>
        <?php endif; ?>
        <form method="POST" action="">
            <input type="text" name="username" placeholder="Korisničko ime ili email" required>
            <input type="password" name="password" placeholder="Lozinka" required>
            <button type="submit">Prijavi se</button>
            <p>Nemate nalog? <a href="index.php">Registruj se ovde</a></p>
            <p>Zaboravili ste lozinku? <a href="forgot_password.php">Kliknite ovde</a></p>
        </form>
    </div>
</body>
</html>
