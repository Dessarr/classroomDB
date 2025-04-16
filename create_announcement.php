<?php
session_start();
require_once 'config.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $class_id = (int)$_POST['class_id'];
    $user_id = $_SESSION['user_id'];
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $content = mysqli_real_escape_string($conn, $_POST['content']);
    
    // Cek apakah user memiliki akses ke kelas tersebut
    if ($_SESSION['role'] == 'teacher') {
        $check_query = "SELECT * FROM classes WHERE id = $class_id AND teacher_id = $user_id";
    } else {
        $check_query = "SELECT * FROM class_members WHERE class_id = $class_id AND student_id = $user_id";
    }
    
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) == 0) {
        $_SESSION['error'] = "Anda tidak memiliki akses untuk membuat pengumuman di kelas ini";
        header("Location: class_detail.php?id=$class_id");
        exit();
    }
    
    // Insert data pengumuman baru
    $query = "INSERT INTO announcements (class_id, user_id, title, content) 
              VALUES ($class_id, $user_id, '$title', '$content')";
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = "Pengumuman berhasil dibuat";
        header("Location: class_detail.php?id=$class_id");
        exit();
    } else {
        $_SESSION['error'] = "Error: " . mysqli_error($conn);
        header("Location: class_detail.php?id=$class_id");
        exit();
    }
}
?> 