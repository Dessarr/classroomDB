<?php
session_start();
require_once 'config.php';

// Cek apakah user sudah login dan role-nya teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $teacher_id = $_SESSION['user_id'];
    $class_name = mysqli_real_escape_string($conn, $_POST['class_name']);
    
    // Generate kode kelas unik
    $class_code = substr(md5(uniqid(rand(), true)), 0, 6);
    
    // Insert data kelas baru
    $query = "INSERT INTO classes (teacher_id, class_name, class_code) VALUES ('$teacher_id', '$class_name', '$class_code')";
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = "Kelas berhasil dibuat! Kode kelas: " . $class_code;
        header("Location: teacher_dashboard.php");
        exit();
    } else {
        $_SESSION['error'] = "Error: " . mysqli_error($conn);
        header("Location: teacher_dashboard.php");
        exit();
    }
}
?>