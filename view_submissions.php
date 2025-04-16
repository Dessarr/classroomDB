<?php
session_start();
require_once 'config.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Cek apakah ada ID tugas yang diberikan
if (!isset($_GET['id'])) {
    header("Location: teacher_dashboard.php");
    exit();
}

$assignment_id = (int)$_GET['id'];

// Ambil detail tugas
$assignment_query = "SELECT a.*, c.class_name FROM assignments a 
                    JOIN classes c ON a.class_id = c.id 
                    WHERE a.id = $assignment_id";
$assignment_result = mysqli_query($conn, $assignment_query);

if (mysqli_num_rows($assignment_result) == 0) {
    header("Location: teacher_dashboard.php");
    exit();
}

$assignment = mysqli_fetch_assoc($assignment_result);

// Cek apakah user adalah guru yang membuat kelas ini
if ($_SESSION['role'] == 'teacher') {
    $check_teacher_query = "SELECT * FROM classes WHERE id = {$assignment['class_id']} AND teacher_id = {$_SESSION['user_id']}";
    $check_teacher_result = mysqli_query($conn, $check_teacher_query);
    
    if (mysqli_num_rows($check_teacher_result) == 0) {
        header("Location: teacher_dashboard.php");
        exit();
    }
} else {
    // Jika siswa, cek apakah dia anggota kelas ini
    $check_student_query = "SELECT * FROM class_members WHERE class_id = {$assignment['class_id']} AND student_id = {$_SESSION['user_id']}";
    $check_student_result = mysqli_query($conn, $check_student_query);
    
    if (mysqli_num_rows($check_student_result) == 0) {
        header("Location: student_dashboard.php");
        exit();
    }
}

// Ambil daftar pengumpulan tugas
$submissions_query = "SELECT s.*, u.username, u.full_name 
                     FROM submissions s
                     JOIN users u ON s.student_id = u.id
                     WHERE s.assignment_id = $assignment_id
                     ORDER BY s.submitted_at DESC";
$submissions_result = mysqli_query($conn, $submissions_query);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lihat Pengumpulan Tugas - Classroom</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="teacher_dashboard.php">Classroom</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="teacher_dashboard.php">
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
        <div class="row">
            <div class="col-md-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="teacher_dashboard.php">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="class_detail.php?id=<?php echo $assignment['class_id']; ?>">Kembali ke Kelas</a>
                        </li>
                        <li class="breadcrumb-item active">Pengumpulan Tugas</li>
                    </ol>
                </nav>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="mb-1"><?php echo $assignment['title']; ?></h2>
                        <p class="mb-0"><?php echo $assignment['description']; ?></p>
                        <small class="text-muted">
                            <i class="bi bi-clock"></i>
                            Batas waktu: <?php echo date('d M Y H:i', strtotime($assignment['due_date'])); ?>
                        </small>
                    </div>
                    <a href="class_detail.php?id=<?php echo $assignment['class_id']; ?>"
                        class="btn btn-outline-primary">
                        <i class="bi bi-arrow-left"></i> Kembali ke Kelas
                    </a>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3>Daftar Pengumpulan</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Siswa</th>
                                        <th>File</th>
                                        <th>Waktu Pengumpulan</th>
                                        <th>Status</th>
                                        <th>Nilai</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $no = 1;
                                    while ($submission = mysqli_fetch_assoc($submissions_result)): 
                                        $is_late = strtotime($submission['submitted_at']) > strtotime($assignment['due_date']);
                                    ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><?php echo $submission['full_name']; ?></td>
                                        <td>
                                            <a href="<?php echo $submission['file_path']; ?>" target="_blank"
                                                class="btn btn-sm btn-primary">
                                                <i class="bi bi-download"></i> Download
                                            </a>
                                        </td>
                                        <td><?php echo date('d M Y H:i', strtotime($submission['submitted_at'])); ?>
                                        </td>
                                        <td>
                                            <?php if ($is_late): ?>
                                            <span class="badge bg-warning">Terlambat</span>
                                            <?php else: ?>
                                            <span class="badge bg-success">Tepat Waktu</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($submission['grade']): ?>
                                            <?php echo $submission['grade']; ?>
                                            <?php else: ?>
                                            Belum dinilai
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($_SESSION['role'] == 'teacher'): ?>
                                            <button class="btn btn-sm btn-warning"
                                                onclick="gradeSubmission(<?php echo $submission['id']; ?>)">
                                                <i class="bi bi-pencil"></i> Nilai
                                            </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Nilai -->
    <div class="modal fade" id="gradeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Beri Nilai</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="gradeForm">
                        <input type="hidden" id="submissionId" name="submission_id">
                        <div class="mb-3">
                            <label for="grade" class="form-label">Nilai</label>
                            <input type="number" class="form-control" id="grade" name="grade" min="0" max="100"
                                required>
                        </div>
                        <div class="mb-3">
                            <label for="feedback" class="form-label">Feedback</label>
                            <textarea class="form-control" id="feedback" name="feedback" rows="3"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Simpan Nilai</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function gradeSubmission(submissionId) {
        $('#submissionId').val(submissionId);
        $('#gradeModal').modal('show');
    }

    $('#gradeForm').submit(function(e) {
        e.preventDefault();
        $.ajax({
            url: 'grade_submission.php',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                location.reload();
            }
        });
    });
    </script>
</body>

</html>