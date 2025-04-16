<?php
session_start();
require_once 'config.php';

// Cek apakah user sudah login dan role-nya student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: index.php");
    exit();
}

$student_id = $_SESSION['user_id'];

// Ambil daftar kelas yang diikuti siswa
$classes_query = "SELECT c.* FROM classes c 
                 JOIN class_members cm ON c.id = cm.class_id 
                 WHERE cm.student_id = $student_id";
$classes_result = mysqli_query($conn, $classes_query);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Classroom</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">Classroom</a>
            <div class="navbar-nav ms-auto">
                <span class="nav-item nav-link text-white">Welcome, <?php echo $_SESSION['username']; ?></span>
                <a class="nav-item nav-link" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8">
                <h2>Kelas Saya</h2>
                <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal"
                    data-bs-target="#joinClassModal">
                    Gabung Kelas
                </button>

                <div class="row">
                    <?php while ($class = mysqli_fetch_assoc($classes_result)): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $class['class_name']; ?></h5>
                                <a href="class_detail.php?id=<?php echo $class['id']; ?>" class="btn btn-primary">Lihat
                                    Detail</a>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Join Class -->
    <div class="modal fade" id="joinClassModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Gabung Kelas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="join_class.php" method="POST">
                        <div class="mb-3">
                            <label for="class_code" class="form-label">Kode Kelas</label>
                            <input type="text" class="form-control" id="class_code" name="class_code" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Gabung Kelas</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>