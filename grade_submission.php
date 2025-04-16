<?php
session_start();
require_once 'config.php';

// Cek apakah user adalah guru
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
    die('Unauthorized access');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $submission_id = (int)$_POST['submission_id'];
    $grade = (float)$_POST['grade'];
    $feedback = mysqli_real_escape_string($conn, $_POST['feedback']);
    
    // Update nilai dan feedback
    $query = "UPDATE submissions 
              SET grade = $grade, feedback = '$feedback' 
              WHERE id = $submission_id";
    
    if (mysqli_query($conn, $query)) {
        echo 'success';
    } else {
        echo 'Error: ' . mysqli_error($conn);
    }
}
?>