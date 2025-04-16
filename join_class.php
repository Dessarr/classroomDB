<?php
session_start();
require_once 'config.php';

// Cek apakah user sudah login dan role-nya student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = $_SESSION['user_id'];
    $class_code = mysqli_real_escape_string($conn, $_POST['class_code']);
    
    // Cari kelas berdasarkan kode
    $class_query = "SELECT * FROM classes WHERE class_code = '$class_code'";
    $class_result = mysqli_query($conn, $class_query);
    
    if (mysqli_num_rows($class_result) == 1) {
        $class = mysqli_fetch_assoc($class_result);
        $class_id = $class['id'];
        
        // Cek apakah siswa sudah bergabung dengan kelas tersebut
        $check_query = "SELECT * FROM class_members WHERE class_id = $class_id AND student_id = $student_id";
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $_SESSION['error'] = "Anda sudah bergabung dengan kelas ini";
            header("Location: student_dashboard.php");
            exit();
        }
        
        // Gabung ke kelas
        $join_query = "INSERT INTO class_members (class_id, student_id) VALUES ($class_id, $student_id)";
        
        if (mysqli_query($conn, $join_query)) {
            $_SESSION['success'] = "Berhasil bergabung dengan kelas " . $class['class_name'];
            header("Location: student_dashboard.php");
            exit();
        } else {
            $_SESSION['error'] = "Error: " . mysqli_error($conn);
            header("Location: student_dashboard.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "Kode kelas tidak valid";
        header("Location: student_dashboard.php");
        exit();
    }
}
?>