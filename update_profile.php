<?php
session_start();
include 'db_config.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$new_full_name = $conn->real_escape_string($_POST['new_full_name']);
$new_email = $conn->real_escape_string($_POST['new_email']);

$query = "SELECT last_updated FROM users WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($last_updated);
$stmt->fetch();
$stmt->close();

if ($last_updated) {
    $last_update_time = strtotime($last_updated);
    $current_time = time();
    $time_diff = $current_time - $last_update_time;

    if ($time_diff < 604800) { // week
        header("Location: dashboard.php?error=Ne možete menjati podatke više od jednom u 7 dana");
        exit();
    }
}

$query = "UPDATE users SET full_name = ?, email = ?, last_updated = NOW() WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("sss", $new_full_name, $new_email, $username);

if ($stmt->execute()) {
    echo "Podaci su uspešno ažurirani!";
    header("Location: dashboard.php");
} else {
    echo "Greška pri ažuriranju podataka!";
}

$stmt->close();
$conn->close();
?>
