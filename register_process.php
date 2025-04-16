<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);

    // Cek apakah username sudah ada
    $check_query = "SELECT * FROM users WHERE username = '$username'";
    $check_result = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        $_SESSION['error'] = "Username sudah digunakan";
        header("Location: register.php");
        exit();
    }

    // Cek apakah email sudah ada
    $check_email = "SELECT * FROM users WHERE email = '$email'";
    $check_email_result = mysqli_query($conn, $check_email);

    if (mysqli_num_rows($check_email_result) > 0) {
        $_SESSION['error'] = "Email sudah digunakan";
        header("Location: register.php");
        exit();
    }

    // Insert data user baru
    $query = "INSERT INTO users (username, email, password, full_name, role) VALUES ('$username', '$email', '$password', '$full_name', '$role')";
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = "Pendaftaran berhasil! Silakan login.";
        header("Location: index.php");
        exit();
    } else {
        $_SESSION['error'] = "Error: " . mysqli_error($conn);
        header("Location: register.php");
        exit();
    }
}
?>