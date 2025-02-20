<?php
session_start();
include 'db_config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: index.php");
    exit;
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_role'])) {
    $user_id = intval($_POST['user_id']);
    $new_role = $_POST['role'];

    if ($new_role === 'Admin' || $new_role === 'User') {
        $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->bind_param("si", $new_role, $user_id);
        if ($stmt->execute()) {
            $message = "Rank korisnika je promenjen.";
        } else {
            $message = "Greška: " . $conn->error;
        }
        $stmt->close();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_notes'])) {
    $user_id = intval($_POST['user_id']);
    $notes = $_POST['notes'];

    $stmt = $conn->prepare("UPDATE users SET notes = ? WHERE id = ?");
    $stmt->bind_param("si", $notes, $user_id);
    if ($stmt->execute()) {
        $message = "Beleška je sačuvana.";
    } else {
        $message = "Greška: " . $conn->error;
    }
    $stmt->close();
}

if (isset($_POST['update_payment_status'])) {
    $user_id = intval($_POST['user_id']);
    $is_paid = $_POST['is_paid'] == 1 ? 1 : 0;

    $sql = "UPDATE users SET is_paid = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $is_paid, $user_id);
    if ($stmt->execute()) {
        $message = "Status plaćanja je ažuriran.";
    } else {
        $message = "Greška pri ažuriranju statusa plaćanja!";
    }
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['ban'])) {
        $user_id = intval($_POST['user_id']);
        $is_banned = 1; // Ban korisnika
    } elseif (isset($_POST['unban'])) {
        $user_id = intval($_POST['user_id']);
        $is_banned = 0; // Unban korisnika
    }

    if (isset($is_banned)) {
        $stmt = $conn->prepare("UPDATE users SET is_banned = ? WHERE id = ?");
        $stmt->bind_param("ii", $is_banned, $user_id);
        if ($stmt->execute()) {
            $message = $is_banned ? "Korisnik je banovan!" : "Korisnik je unbanovan!";
        } else {
            $message = "Greška pri ažuriranju statusa!";
        }
        $stmt->close();
    }
}


$sql = "SELECT id, full_name, username, role, is_banned, is_paid, created_at FROM users";
$result = $conn->query($sql);


$sql = "SELECT id, full_name, username, role, is_banned, is_paid, created_at FROM users";
$result = $conn->query($sql);

$sql = "SELECT id, full_name, username, role, is_banned, is_paid, notes, created_at FROM users";
$result = $conn->query($sql);


$username = $_SESSION['username'];
$query = "SELECT is_paid FROM users WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($is_paid);
$stmt->fetch();
$stmt->close();
$_SESSION['is_paid'] = $is_paid;

   


?>

<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <div class="sidebar">
        <h2>Platform</h2>
        <a href="dashboard.php"><i class="fa-solid fa-user"></i> Profil</a>
        <?php if ($is_paid === 1): ?>
        <a href="premium_services.php"><i class="fa-solid fa-crown"></i> Premium</a>
        <?php endif; ?>
        <a href="admin_panel.php"><i class="fa-solid fa-users"></i> Korisnici</a>
        <a href="statistics.php"><i class="fa-solid fa-chart-bar"></i> Statistika</a>
        <a href="user_logs.php"><i class="fa-solid fa-clipboard-list"></i> Logs</a>
        <a href="user_settings.php"><i class="fa-solid fa-gear"></i> Podešavanja</a>
        <a href="auth.php?logout=true" class="logout"><i class="fa-solid fa-sign-out"></i> Odjavi se</a>
    </div>
    
    <div class="main-content">
        <h1>Upravljanje korisnicima</h1>
        
        <div class="message"> 
            <?php if (!empty($message)) echo "<p>$message</p>"; ?> 
        </div>
        
        <table border="1">
            <tr>
                <th>ID</th>
                <th>Ime i Prezime</th>
                <th>Korisničko ime</th>
                <th>Rank</th>
                <th>Promeni Rank</th>
                <th>Beleške</th>
                <th>Status Plaćanja</th>
                <th>Registracija</th>
                <th>Pristup</th>
            </tr>
            
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['id']); ?></td>
                <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                <td><?php echo htmlspecialchars($row['username']); ?></td>
                <td><?php echo htmlspecialchars($row['role']); ?></td>
                <td>
                    <form method="post">
                        <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                        <select name="role">
                            <option value="User" <?php if($row['role'] === 'User') echo "selected"; ?>>User</option>
                            <option value="Admin" <?php if($row['role'] === 'Admin') echo "selected"; ?>>Admin</option>
                        </select>
                        <button type="submit" name="update_role">Promeni</button>
                    </form>
                </td>
                <td>
                    <form method="post">
                        <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                        <textarea name="notes" rows="2"><?php echo htmlspecialchars($row['notes']); ?></textarea>
                        <button type="submit" name="update_notes">Sačuvaj</button>
                    </form>
                </td>
                <td>
                    <form method="post">
                        <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                        <select name="is_paid">
                            <option value="1" <?php if($row['is_paid'] == 1) echo "selected"; ?>>Plaćeno</option>
                            <option value="0" <?php if($row['is_paid'] == 0) echo "selected"; ?>>Nije plaćeno</option>
                        </select>
                        <button type="submit" name="update_payment_status">Ažuriraj</button>
                    </form>
                </td>
                <td><?php echo date('d.m.Y', strtotime($row['created_at'])); ?></td>
                <td>
                    <form action="admin_panel.php" method="post">
                        <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                        <?php if ($row['is_banned'] == 1): ?>
                            <button id="unbanid" class="unban" type="submit" name="unban">Unban</button>
                        <?php else: ?>
                            <button id="banid" class="ban" type="submit" name="ban">Ban</button>
                        <?php endif; ?>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
        <br>
        <form action="export_csv.php" method="post" class="export-button">
            <button type="submit">📂 Preuzmi listu korisnika</button>
        </form>
    </div>
</body>
</html>
