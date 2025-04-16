<?php
session_start();
require_once 'config.php';

// Cek apakah user adalah admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('HTTP/1.1 403 Forbidden');
    exit(json_encode(['success' => false, 'message' => 'Akses ditolak']));
}

// Cek apakah request adalah POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    exit(json_encode(['success' => false, 'message' => 'Metode tidak diizinkan']));
}

// Validasi input
$required_fields = ['id', 'role', 'username', 'email', 'full_name'];
foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || empty($_POST[$field])) {
        header('HTTP/1.1 400 Bad Request');
        exit(json_encode(['success' => false, 'message' => "Field $field wajib diisi"]));
    }
}

$user_id = (int)$_POST['id'];
$role = $_POST['role'];
$username = mysqli_real_escape_string($conn, $_POST['username']);
$email = mysqli_real_escape_string($conn, $_POST['email']);
$full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
$password = isset($_POST['password']) && !empty($_POST['password']) ? 
           password_hash($_POST['password'], PASSWORD_DEFAULT) : null;

// Validasi role
if ($role != 'teacher' && $role != 'student') {
    header('HTTP/1.1 400 Bad Request');
    exit(json_encode(['success' => false, 'message' => 'Role tidak valid']));
}

// Cek apakah username sudah digunakan (kecuali oleh user yang sedang diedit)
$query = "SELECT id FROM users WHERE username = '$username' AND id != $user_id";
$result = mysqli_query($conn, $query);
if (mysqli_num_rows($result) > 0) {
    header('HTTP/1.1 400 Bad Request');
    exit(json_encode(['success' => false, 'message' => 'Username sudah digunakan']));
}

// Cek apakah email sudah digunakan (kecuali oleh user yang sedang diedit)
$query = "SELECT id FROM users WHERE email = '$email' AND id != $user_id";
$result = mysqli_query($conn, $query);
if (mysqli_num_rows($result) > 0) {
    header('HTTP/1.1 400 Bad Request');
    exit(json_encode(['success' => false, 'message' => 'Email sudah digunakan']));
}

// Update data user
if ($password) {
    $query = "UPDATE users SET 
              username = '$username',
              email = '$email',
              full_name = '$full_name',
              password = '$password'
              WHERE id = $user_id AND role = '$role'";
} else {
    $query = "UPDATE users SET 
              username = '$username',
              email = '$email',
              full_name = '$full_name'
              WHERE id = $user_id AND role = '$role'";
}

if (mysqli_query($conn, $query)) {
    if (mysqli_affected_rows($conn) > 0) {
        echo json_encode(['success' => true, 'message' => 'Data berhasil diperbarui']);
    } else {
        header('HTTP/1.1 404 Not Found');
        echo json_encode(['success' => false, 'message' => 'Pengguna tidak ditemukan']);
    }
} else {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['success' => false, 'message' => 'Gagal memperbarui data: ' . mysqli_error($conn)]);
}
?>