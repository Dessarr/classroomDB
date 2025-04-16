<?php
session_start();
require_once 'config.php';

// Cek apakah user adalah guru
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
    die('Unauthorized access');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = (int)$_POST['id'];
    $role = $_POST['role'];
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    
    // Cek apakah username sudah ada (kecuali untuk user yang sedang diupdate)
    $check_query = "SELECT * FROM users WHERE username = '$username' AND id != $id";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        die('Username sudah digunakan');
    }
    
    // Cek apakah email sudah ada (kecuali untuk user yang sedang diupdate)
    $check_email = "SELECT * FROM users WHERE email = '$email' AND id != $id";
    $check_email_result = mysqli_query($conn, $check_email);
    
    if (mysqli_num_rows($check_email_result) > 0) {
        die('Email sudah digunakan');
    }
    
    // Update data user
    $query = "UPDATE users SET username = '$username', email = '$email', full_name = '$full_name'";
    
    // Jika password diisi, update password
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $query .= ", password = '$password'";
    }
    
    $query .= " WHERE id = $id AND role = '$role'";
    
    if (mysqli_query($conn, $query)) {
        echo 'success';
    } else {
        echo 'Error: ' . mysqli_error($conn);
    }
}
?>