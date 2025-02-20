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

$email = "";

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

$stats = [
    'total_users' => 0,
    'banned_users' => 0,
    'paid_users' => 0
];

if ($role === 'Admin') {
    $query = "SELECT 
                (SELECT COUNT(*) FROM users) AS total_users,
                (SELECT COUNT(*) FROM users WHERE is_banned = 1) AS banned_users,
                (SELECT COUNT(*) FROM users WHERE is_paid = 1) AS paid_users";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $stmt->bind_result($stats['total_users'], $stats['banned_users'], $stats['paid_users']);
    $stmt->fetch();
    $stmt->close();
}

$query = "SELECT is_paid FROM users WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($is_paid);
$stmt->fetch();
$stmt->close();

$status_placanja = ($is_paid == 1) ? "Plaćeno" : "Nije plaćeno";


$query = "SELECT email, created_at FROM users WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($email, $created_at);
$stmt->fetch();
$stmt->close();


$formatted_date = date('d.m.Y', strtotime($created_at));

$activity_query = "SELECT COUNT(*) as logins, MAX(login_time) as last_login FROM user_logs WHERE username = ?";
$stmt = $conn->prepare($activity_query);
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($logins, $last_login);
$stmt->fetch();
$stmt->close();

$time_ago = strtotime($last_login);
$diff = time() - $time_ago;

if ($diff < 60) {
    $formatted_last_login = "Pre par sekundi";
} elseif ($diff < 3600) {
    $formatted_last_login = "Pre " . floor($diff / 60) . " min";
} elseif ($diff < 86400) {
    $formatted_last_login = "Pre " . floor($diff / 3600) . " sati";
} else {
    $formatted_last_login = "Pre " . floor($diff / 86400) . " dana";
}


$query = "SELECT is_paid FROM users WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($is_paid);
$stmt->fetch();
$stmt->close();
?>


<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Home</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/dashboard.css?v=1">
</head>
<body>
    <div class="sidebar">
        <h2>Platform </h2>
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
    <div class="main-content">
        <h1>Dobrodošao, <?php echo htmlspecialchars($full_name); ?>!</h1>
        <div class="dashboard-container">
        <div class="main-card">
        <?php if ($role === 'Admin'): ?>
        <div class="card5">
        <h2>Korisnik</h2>
            <p><i class="fa-solid fa-user-shield"></i><strong>Rank:</strong> <?php echo htmlspecialchars($role); ?></p>
            <p><i class="fa-solid fa-wallet"></i> <strong>Premium usluge:</strong> <?php echo htmlspecialchars($status_placanja); ?></p>
                <br>
            <p><i class="fa-solid fa-arrow-right-to-bracket"></i> <strong>Ukupan broj prijava:</strong> <?php echo $logins; ?></p>
            <p><i class="fa-solid fa-clock"></i> <strong>Poslednja prijava:</strong> <?php echo $formatted_last_login ? $formatted_last_login : "Nema podataka"; ?></p>
            <p><i class="fa-solid fa-calendar-check"></i> <strong>Datum registracije:</strong> <?php echo $formatted_date; ?></p>
        </div>
        <div class="card">
        <h2>Statistika Sajta</h2>
            <p><i class="fa-solid fa-users"></i> <strong>Ukupan broj korisnika:</strong> <?php echo $stats['total_users']; ?></p>
            <p><i class="fa-solid fa-user-slash"></i> <strong>Banovani korisnici:</strong> <?php echo $stats['banned_users']; ?></p>
            <p><i class="fa-solid fa-crown"></i> <strong>Plaćeni korisnici:</strong> <?php echo $stats['paid_users']; ?></p>
        </div>
        <?php else: ?>
        <div class="card5">
        <h2>Korisnik</h2>
            <p><i class="fa-solid fa-user-shield"></i><strong>Rank:</strong> <?php echo htmlspecialchars($role); ?></p>
            <p><i class="fa-solid fa-wallet"></i> <strong>Premium usluge:</strong> <?php echo htmlspecialchars($status_placanja); ?></p>
            <br>
            <p><i class="fa-solid fa-arrow-right-to-bracket"></i> <strong>Ukupan broj prijava:</strong> <?php echo $logins; ?></p>
            <p><i class="fa-solid fa-clock"></i> <strong>Poslednja prijava:</strong> <?php echo $formatted_last_login ? $formatted_last_login : "Nema podataka"; ?></p>
            <p><i class="fa-solid fa-calendar-check"></i> <strong>Datum registracije:</strong> <?php echo $formatted_date; ?></p>
        </div>
        <?php endif; ?>
    </div>
    <?php if ($role === 'Admin'): ?>
    <div class="task-button-container">
    <button class="add-task-btn" onclick="openTaskModal()">
        <i class="fa-solid fa-plus"></i> Novi zadatak
    </button>
    </div>
    <?php endif; ?>
    </div>
    <div id="taskModal" class="modal">
    <div class="modal-container">
    <div class="modal-content">
        <span class="close" onclick="closeTaskModal()">&times;</span>
        <h2>Dodaj novi zadatak</h2>
        <form method="POST" action="add_task.php">
            <label for="task_title">Naziv zadatka:</label>
            <input type="text" name="task_title" id="task_title" required>

            <label for="task_desc">Opis:</label>
            <textarea name="task_desc" id="task_desc" required></textarea>

            <label for="assigned_to">Dodeli zaposlenom:</label>
            <select name="assigned_to" id="assigned_to">
                <?php
                $users_query = "SELECT username FROM users WHERE role = 'User'";
                $result = $conn->query($users_query);
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='" . htmlspecialchars($row['username']) . "'>" . htmlspecialchars($row['username']) . "</option>";
                }
                ?>
            </select>

            <button type="submit">Dodaj</button>
        </form>
    </div>
    </div>
    </div>
    <br>
    <h2>Moji zadaci</h2>
    <ul id="taskList"></ul>

    </div>
    
    <script src="javascript/tasks.js"></script>
</body>
</html>
