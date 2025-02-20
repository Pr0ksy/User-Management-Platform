<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: index.php");
    exit;
}

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

include('db_config.php');

$username = $_SESSION['username'];
$full_name = $_SESSION['full_name'];
$role = $_SESSION['role'];

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

$query = "SELECT is_paid FROM users WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($is_paid);
$stmt->fetch();
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['clear_logs'])) {
    if ($role === 'Admin') { // Samo admin može da briše logove
        $delete_query = "DELETE FROM user_logs";
        if ($conn->query($delete_query) === TRUE) {
            echo "<p style='color: green;'>Svi logovi su obrisani!</p>";
        } else {
            echo "<p style='color: red;'>Greška pri brisanju logova: " . $conn->error . "</p>";
        }
    } else {
        echo "<p style='color: red;'>Nemate dozvolu za ovu akciju!</p>";
    }
}



?>

<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/logs.css">
    <title>Dashboard | User Logs</title>
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
    <?php if ($role === 'Admin'): ?>
    <div class="main-content"></div>
    <div class="content-wrapper">
        <h1>Prijave korisnika</h1>
    <table border="1">
        <tr>
            <th>Korisničko ime</th>
            <th>IP adresa</th>
            <th>Vreme prijave</th>
            <th>Status</th>
        </tr>
        <?php
        $log_query = "SELECT username, ip_address, login_time, success FROM user_logs ORDER BY login_time DESC LIMIT 20";
        $log_result = $conn->query($log_query);
        
        while ($log = $log_result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($log['username']) . "</td>";
            echo "<td>" . htmlspecialchars($log['ip_address']) . "</td>";
            echo "<td>" . $log['login_time'] . "</td>";
            echo "<td>" . ($log['success'] ? "Uspešno" : "Neuspešno") . "</td>";
            echo "</tr>";
        }
        ?>
    </table>
    <?php endif; ?>
    <br>
    <form action="export_csv_logs.php" method="post" class="export-button">
            <button class="btn2" type="submit">📂 Preuzmi Logs</button>
    </form>
    <form method="post" action="">
    <div class="btn-container">
        <button class="btn" type="submit" name="clear_logs" onclick="return confirm('Da li ste sigurni da želite da obrišete sve logove?')">
            Obriši sve logove
        </button>
    </div>
    </form>

    </div>
</body>
<?php $conn->close(); ?>
</html>
