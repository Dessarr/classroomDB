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
    
    // Cek apakah user yang akan dihapus adalah diri sendiri
    if ($id == $_SESSION['user_id']) {
        die('Tidak dapat menghapus akun sendiri');
    }
    
    // Hapus user
    $query = "DELETE FROM users WHERE id = $id AND role = '$role'";
    
    if (mysqli_query($conn, $query)) {
        echo 'success';
    } else {
        echo 'Error: ' . mysqli_error($conn);
    }
}
?>