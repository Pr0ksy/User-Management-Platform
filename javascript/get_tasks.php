<?php
include 'db_config.php';
session_start();

if (!isset($_SESSION['username'])) {
    echo json_encode(["error" => "Niste prijavljeni."]);
    exit;
}

$loggedInUser = $_SESSION['username']; 

$sql = "SELECT id, title, description, status FROM tasks WHERE assigned_to = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $loggedInUser);
$stmt->execute();
$result = $stmt->get_result();

$tasks = [];
while ($row = $result->fetch_assoc()) {
    $tasks[] = $row;
}

echo json_encode($tasks);
?>
