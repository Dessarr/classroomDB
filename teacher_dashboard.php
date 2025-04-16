<?php
session_start();
require_once 'config.php';

// Cek apakah user sudah login dan role-nya teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
    header("Location: index.php");
    exit();
}

$teacher_id = $_SESSION['user_id'];

// Ambil daftar kelas yang dibuat oleh guru
$classes_query = "SELECT * FROM classes WHERE teacher_id = $teacher_id";
$classes_result = mysqli_query($conn, $classes_query);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard - Classroom</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">Classroom</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-item nav-link" href="manage_users.php">Kelola Pengguna</a>
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
                    data-bs-target="#createClassModal">
                    Buat Kelas Baru
                </button>

                <div class="row">
                    <?php while ($class = mysqli_fetch_assoc($classes_result)): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $class['class_name']; ?></h5>
                                <p class="card-text">Kode Kelas: <?php echo $class['class_code']; ?></p>
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

    <!-- Modal Create Class -->
    <div class="modal fade" id="createClassModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Buat Kelas Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="create_class.php" method="POST">
                        <div class="mb-3">
                            <label for="class_name" class="form-label">Nama Kelas</label>
                            <input type="text" class="form-control" id="class_name" name="class_name" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Buat Kelas</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>