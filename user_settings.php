<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

include('db_config.php');

// ✅ Postavljamo username ODMAH na početku
$username = $_SESSION['username'];
$full_name = $_SESSION['full_name'];
$role = $_SESSION['role'];

// ✅ Provera da li je korisnik banovan
$query = "SELECT is_banned FROM users WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($is_banned);
$stmt->fetch();
$stmt->close();

if ($is_banned == 1) {
    session_destroy();
    header("Location: login.php?error=ban");
    exit();
}

// ✅ Provera da li je korisnik platio
$query = "SELECT is_paid FROM users WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($is_paid);
$stmt->fetch();
$stmt->close();

$status_placanja = ($is_paid == 1) ? "Plaćeno" : "Nije plaćeno";


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['change_password'])) {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        echo "Greška: Nove šifre se ne poklapaju!";
    } else {
        $query = "SELECT password FROM users WHERE username = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->bind_result($hashed_password);
        $stmt->fetch();
        $stmt->close();

        if (!password_verify($old_password, $hashed_password)) {
            echo "Greška: Stara šifra nije tačna!";
        } else {
            $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_query = "UPDATE users SET password = ? WHERE username = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("ss", $new_hashed_password, $username);

            if ($update_stmt->execute()) {
                echo "Uspešno promenjena šifra!";
            } else {
                echo "Greška pri promeni šifre!";
            }

            $update_stmt->close();
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_account'])) {
    $delete_query = "DELETE FROM users WHERE username = ?";
    $delete_stmt = $conn->prepare($delete_query);
    $delete_stmt->bind_param("s", $username);

    if ($delete_stmt->execute()) {
        session_destroy();
        header("Location: login.php");
        exit();
    } else {
        echo "Greška pri brisanju naloga!";
    }
}

$email = ""; // Postavljamo podrazumevanu vrednost kako bi izbegli grešku

if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];

    $query = "SELECT email FROM users WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($email);
    $stmt->fetch();
    $stmt->close();
}


?>


<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Settings</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/settings.css">
</head>
<body>
    <div class="sidebar">
        <h2>Platform</h2>
        <a href="dashboard.php"><i class="fa-solid fa-user"></i> Profil</a>     
        <?php if ($is_paid === 1): ?>
        <a href="premium_services.php"><i class="fa-solid fa-crown"></i> Premium</a>
        <?php endif; ?>
        <?php if ($role === 'Admin'): ?>
        <a href="admin_panel.php"><i class="fa-solid fa-users"></i> Korisnici</a>
        <?php endif; ?>
        <a href="statistics.php"><i class="fa-solid fa-chart-bar"></i> Statistika</a>
        <?php if ($role === 'Admin'): ?>
        <a href="user_logs.php"><i class="fa-solid fa-clipboard-list"></i> Logs</a>
        <?php endif; ?>
        <a href="user_settings.php"><i class="fa-solid fa-gear"></i> Podešavanja</a>
        <a href="auth.php?logout=true" class="logout"><i class="fa-solid fa-sign-out"></i> Odjavi se</a>
    </div>
    </div>
    <div class="main-content">
        <h1>Podešavanja</h1>
        <div class="card">
            <h2>Promena Informacija</h2>
            <form action="update_profile.php" method="post">
                <label for="new_full_name">Novo ime:</label>
                <input type="text" name="new_full_name" value="<?php echo htmlspecialchars($full_name); ?>" required><br><br>
                <label for="new_email">Novi email:</label>
                <input type="email" name="new_email" value="<?php echo htmlspecialchars($email); ?>" required><br><br>
                <button class="button" type="submit" name="update_profile">Sačuvaj promene</button>
            </form>
        </div>
        <div class="card">
            <h2>Promena šifre</h2>
            <form action="dashboard.php" method="post">
                <label for="old_password">Stara šifra:</label>
                <input type="password" name="old_password" required><br><br>
                <label for="new_password">Nova šifra:</label>
                <input type="password" name="new_password" required><br><br>
                <label for="confirm_password">Potvrdi novu šifru:</label>
                <input type="password" name="confirm_password" required><br><br>
                <button class="button" type="submit" name="change_password">Promeni šifru</button>
            </form>
        </div>
        <div class="card delete-account">
            <h2>Brisanje naloga</h2>
            <form action="dashboard.php" method="post" onsubmit="return confirm('Da li ste sigurni da želite da obrišete svoj nalog? Ova radnja je nepovratna.');">
                <button class="button delete" type="submit" name="delete_account">Obriši nalog</button>
            </form>
        </div>
    </div>
</body>
</html>
