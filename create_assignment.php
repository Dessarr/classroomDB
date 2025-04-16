<?php
session_start();
require_once 'config.php';

// Cek apakah user sudah login dan role-nya teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $class_id = (int)$_POST['class_id'];
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $due_date = mysqli_real_escape_string($conn, $_POST['due_date']);
    
    // Cek apakah kelas tersebut milik guru yang sedang login
    $check_query = "SELECT * FROM classes WHERE id = $class_id AND teacher_id = " . $_SESSION['user_id'];
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) == 0) {
        $_SESSION['error'] = "Anda tidak memiliki akses untuk membuat tugas di kelas ini";
        header("Location: class_detail.php?id=$class_id");
        exit();
    }
    
    // Insert data tugas baru
    $query = "INSERT INTO assignments (class_id, title, description, due_date) 
              VALUES ($class_id, '$title', '$description', '$due_date')";
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = "Tugas berhasil dibuat";
        header("Location: class_detail.php?id=$class_id");
        exit();
    } else {
        $_SESSION['error'] = "Error: " . mysqli_error($conn);
        header("Location: class_detail.php?id=$class_id");
        exit();
    }
}
?> 