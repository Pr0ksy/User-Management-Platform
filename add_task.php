<?php
session_start();
require 'db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION["role"]) && $_SESSION["role"] === "Admin") {
    $title = $_POST["task_title"];
    $desc = $_POST["task_desc"];
    $assigned_to = $_POST["assigned_to"];
    $created_by = $_SESSION["username"];

    $query = "INSERT INTO tasks (title, description, assigned_to, created_by, status) VALUES (?, ?, ?, ?, 'pending')";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssss", $title, $desc, $assigned_to, $created_by);

    if ($stmt->execute()) {
        header("Location: dashboard.php?success=TaskAdded");
    } else {
        header("Location: dashboard.php?error=TaskError");
    }
}




?>