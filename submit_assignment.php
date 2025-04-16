<?php
session_start();
require_once 'config.php';

// Cek apakah user sudah login dan role-nya student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: index.php");
    exit();
}

$student_id = $_SESSION['user_id'];
$assignment_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Ambil detail tugas
$assignment_query = "SELECT a.*, c.id as class_id 
                    FROM assignments a 
                    JOIN classes c ON a.class_id = c.id 
                    WHERE a.id = $assignment_id";
$assignment_result = mysqli_query($conn, $assignment_query);

if (mysqli_num_rows($assignment_result) == 0) {
    header("Location: student_dashboard.php");
    exit();
}

$assignment = mysqli_fetch_assoc($assignment_result);

// Cek apakah siswa memiliki akses ke kelas tersebut
$access_query = "SELECT * FROM class_members WHERE class_id = " . $assignment['class_id'] . " AND student_id = $student_id";
$access_result = mysqli_query($conn, $access_query);

if (mysqli_num_rows($access_result) == 0) {
    header("Location: student_dashboard.php");
    exit();
}

// Cek apakah sudah ada submission sebelumnya
$submission_query = "SELECT * FROM submissions WHERE assignment_id = $assignment_id AND student_id = $student_id";
$submission_result = mysqli_query($conn, $submission_query);
$has_submission = mysqli_num_rows($submission_result) > 0;
$submission = $has_submission ? mysqli_fetch_assoc($submission_result) : null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle file upload hanya jika belum dinilai
    if (!$has_submission || ($has_submission && $submission['grade'] === null)) {
        if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
            $file = $_FILES['file'];
            $file_name = $file['name'];
            $file_tmp = $file['tmp_name'];
            $file_size = $file['size'];
            
            // Generate unique filename
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $new_file_name = uniqid() . '.' . $file_ext;
            
            // Create uploads directory if not exists
            if (!file_exists('uploads')) {
                mkdir('uploads', 0777, true);
            }
            
            $upload_path = 'uploads/' . $new_file_name;
            
            if (move_uploaded_file($file_tmp, $upload_path)) {
                if ($has_submission) {
                    // Update existing submission
                    $query = "UPDATE submissions SET file_path = '$upload_path', submitted_at = NOW() 
                             WHERE assignment_id = $assignment_id AND student_id = $student_id";
                } else {
                    // Create new submission
                    $query = "INSERT INTO submissions (assignment_id, student_id, file_path) 
                             VALUES ($assignment_id, $student_id, '$upload_path')";
                }
                
                if (mysqli_query($conn, $query)) {
                    $_SESSION['success'] = "Tugas berhasil dikumpulkan";
                    header("Location: class_detail.php?id=" . $assignment['class_id']);
                    exit();
                } else {
                    $_SESSION['error'] = "Error: " . mysqli_error($conn);
                }
            } else {
                $_SESSION['error'] = "Gagal mengupload file";
            }
        } else {
            $_SESSION['error'] = "Silakan pilih file untuk dikumpulkan";
        }
    } else {
        $_SESSION['error'] = "Tugas sudah dinilai dan tidak dapat diubah";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kumpulkan Tugas - Classroom</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="student_dashboard.php">Classroom</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="student_dashboard.php">
                            <i class="bi bi-house-door"></i> Dashboard
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <span class="nav-link text-white">Welcome, <?php echo $_SESSION['username']; ?></span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="student_dashboard.php">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="class_detail.php?id=<?php echo $assignment['class_id']; ?>">Kembali ke Kelas</a>
                        </li>
                        <li class="breadcrumb-item active">Kumpulkan Tugas</li>
                    </ol>
                </nav>

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="mb-0">Kumpulkan Tugas</h3>
                        <a href="class_detail.php?id=<?php echo $assignment['class_id']; ?>"
                            class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-arrow-left"></i> Kembali ke Kelas
                        </a>
                    </div>
                    <div class="card-body">
                        <h5><?php echo $assignment['title']; ?></h5>
                        <p><?php echo $assignment['description']; ?></p>
                        <p>
                            <i class="bi bi-clock"></i>
                            Batas waktu: <?php echo date('d M Y H:i', strtotime($assignment['due_date'])); ?>
                        </p>

                        <?php if ($has_submission): ?>
                        <div class="alert alert-info">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                <strong>Status: Sudah Dikumpulkan</strong>
                            </div>
                            <p class="mb-1">Waktu pengumpulan:
                                <?php echo date('d M Y H:i', strtotime($submission['submitted_at'])); ?></p>
                            <p class="mb-1">File: <a href="<?php echo $submission['file_path']; ?>"
                                    target="_blank"><?php echo basename($submission['file_path']); ?></a></p>

                            <?php if ($submission['grade'] !== null): ?>
                            <div class="mt-3 p-3 bg-light rounded">
                                <h6 class="mb-2"><i class="bi bi-star-fill text-warning"></i> Nilai:
                                    <?php echo $submission['grade']; ?></h6>
                                <?php if ($submission['feedback']): ?>
                                <div class="mt-2">
                                    <strong>Feedback:</strong>
                                    <p class="mb-0"><?php echo $submission['feedback']; ?></p>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php else: ?>
                            <div class="mt-3">
                                <p class="text-muted mb-0"><i class="bi bi-hourglass-split"></i> Menunggu penilaian</p>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <?php if (!$has_submission || ($has_submission && $submission['grade'] === null)): ?>
                        <form action="" method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="file" class="form-label">File Tugas</label>
                                <input type="file" class="form-control" id="file" name="file" required>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <?php echo $has_submission ? 'Update Tugas' : 'Kumpulkan Tugas'; ?>
                            </button>
                        </form>
                        <?php else: ?>
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            Tugas sudah dinilai dan tidak dapat diubah
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>