<?php
session_start();
include 'db_config.php';

if (isset($_POST['register'])) {
    $user = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $full_name = $conn->real_escape_string($_POST['full_name']);
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    $role = 'User';
    $is_paid = 0;

    $sql = "INSERT INTO users (username, email, full_name, password, role, is_paid) 
            VALUES ('$user', '$email', '$full_name', '$pass', '$role', '$is_paid')";
    
    if ($conn->query($sql) === TRUE) {
        header("Location: login.php?message=success");
        exit();
    } else {
        header("Location: index.php?message=error");
        exit();
    }
}

// Login
if (isset($_POST['login'])) {
    $user = $conn->real_escape_string($_POST['username']); 
    $pass = $_POST['password'];
    
    // 🔍 Dobijamo korisnika iz baze
    $sql = "SELECT id, username, full_name, password, role, is_banned FROM users WHERE username=? OR email=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $user, $user);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $username, $full_name, $hashed_password, $role, $is_banned);
        $stmt->fetch();

        var_dump($is_banned);
        exit();

        if ($is_banned == 1) {
            echo "Vaš nalog je blokiran! Ne možete se prijaviti.";
            exit();
        }


        if (password_verify($pass, $hashed_password)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $username;
            $_SESSION['full_name'] = $full_name;
            $_SESSION['role'] = $role;
            
            header("Location: dashboard.php");
            exit();
        } else {
            echo "Pogrešna lozinka!";
        }
    } else {
        echo "Korisnik ne postoji!";
    }

    $stmt->close();
}



//Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit;
}



$conn->close();
?>
