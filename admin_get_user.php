<?php
session_start();
require_once 'config.php';

// Cek apakah user adalah admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    die('Unauthorized access');
}

if (isset($_GET['id']) && isset($_GET['role'])) {
    $id = (int)$_GET['id'];
    $role = $_GET['role'];
    
    $query = "SELECT * FROM users WHERE id = $id AND role = '$role'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        // Jika request untuk view
        if (!isset($_GET['edit'])) {
            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<p><strong>Username:</strong> ' . $user['username'] . '</p>';
            echo '<p><strong>Email:</strong> ' . $user['email'] . '</p>';
            echo '<p><strong>Nama Lengkap:</strong> ' . $user['full_name'] . '</p>';
            echo '<p><strong>Role:</strong> ' . ucfirst($user['role']) . '</p>';
            echo '<p><strong>Tanggal Dibuat:</strong> ' . date('d M Y H:i', strtotime($user['created_at'])) . '</p>';
            echo '</div>';
            echo '</div>';
        } else {
            // Jika request untuk edit
            echo json_encode($user);
        }
    }
}
?>