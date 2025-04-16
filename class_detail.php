<?php
session_start();
require_once 'config.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Ambil ID kelas dari URL
$class_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Ambil detail kelas
$class_query = "SELECT c.*, u.username as teacher_name 
                FROM classes c 
                JOIN users u ON c.teacher_id = u.id 
                WHERE c.id = $class_id";
$class_result = mysqli_query($conn, $class_query);

if (mysqli_num_rows($class_result) == 0) {
    header("Location: " . ($role == 'teacher' ? 'teacher_dashboard.php' : 'student_dashboard.php'));
    exit();
}

$class = mysqli_fetch_assoc($class_result);

// Cek apakah user memiliki akses ke kelas ini
if ($role == 'student') {
    $access_query = "SELECT * FROM class_members WHERE class_id = $class_id AND student_id = $user_id";
    $access_result = mysqli_query($conn, $access_query);
    
    if (mysqli_num_rows($access_result) == 0) {
        header("Location: student_dashboard.php");
        exit();
    }
}

// Ambil daftar tugas
$assignments_query = "SELECT a.*, 
                            CASE 
                                WHEN s.id IS NOT NULL THEN 1 
                                ELSE 0 
                            END as is_submitted,
                            s.grade,
                            s.submitted_at,
                            CASE
                                WHEN s.submitted_at > a.due_date THEN 1
                                ELSE 0
                            END as is_late
                     FROM assignments a 
                     LEFT JOIN submissions s ON a.id = s.assignment_id AND s.student_id = $user_id
                     WHERE a.class_id = $class_id 
                     ORDER BY a.due_date ASC";
$assignments_result = mysqli_query($conn, $assignments_query);

// Ambil daftar pengumuman
$announcements_query = "SELECT a.*, u.username as author_name 
                       FROM announcements a 
                       JOIN users u ON a.user_id = u.id 
                       WHERE a.class_id = $class_id 
                       ORDER BY a.created_at DESC";
$announcements_result = mysqli_query($conn, $announcements_query);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $class['class_name']; ?> - Classroom</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand"
                href="<?php echo $role == 'teacher' ? 'teacher_dashboard.php' : 'student_dashboard.php'; ?>">Classroom</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link"
                            href="<?php echo $role == 'teacher' ? 'teacher_dashboard.php' : 'student_dashboard.php'; ?>">
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
            <div class="col-md-8">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a
                                href="<?php echo $role == 'teacher' ? 'teacher_dashboard.php' : 'student_dashboard.php'; ?>">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item active"><?php echo $class['class_name']; ?></li>
                    </ol>
                </nav>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="mb-1"><?php echo $class['class_name']; ?></h2>
                        <p class="mb-0">Dibuat oleh: <?php echo $class['teacher_name']; ?></p>
                    </div>
                    <a href="<?php echo $role == 'teacher' ? 'teacher_dashboard.php' : 'student_dashboard.php'; ?>"
                        class="btn btn-outline-primary">
                        <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
                    </a>
                </div>

                <?php if ($role == 'teacher'): ?>
                <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal"
                    data-bs-target="#createAssignmentModal">
                    <i class="bi bi-plus-circle"></i> Buat Tugas
                </button>
                <?php endif; ?>

                <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal"
                    data-bs-target="#createAnnouncementModal">
                    <i class="bi bi-megaphone"></i> Buat Pengumuman
                </button>

                <h3 class="mt-4">Tugas</h3>
                <div class="list-group mb-4">
                    <?php while ($assignment = mysqli_fetch_assoc($assignments_result)): ?>
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="d-flex align-items-center gap-2">
                                    <h5 class="mb-1"><?php echo $assignment['title']; ?></h5>
                                    <?php if ($role == 'student'): ?>
                                    <?php if ($assignment['is_submitted']): ?>
                                    <span class="badge bg-success">Sudah Dikumpulkan</span>
                                    <?php if ($assignment['is_late']): ?>
                                    <span class="badge bg-warning">Terlambat</span>
                                    <?php endif; ?>
                                    <?php else: ?>
                                    <?php if (strtotime($assignment['due_date']) < time()): ?>
                                    <span class="badge bg-danger">Lewat Batas Waktu</span>
                                    <?php else: ?>
                                    <span class="badge bg-warning">Belum Dikumpulkan</span>
                                    <?php endif; ?>
                                    <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                                <p class="mb-1"><?php echo $assignment['description']; ?></p>
                                <small>
                                    <i class="bi bi-clock"></i>
                                    Batas waktu: <?php echo date('d M Y H:i', strtotime($assignment['due_date'])); ?>
                                </small>
                            </div>
                            <div class="text-end">
                                <?php if ($role == 'student'): ?>
                                <?php if ($assignment['is_submitted']): ?>
                                <div class="d-flex flex-column align-items-end">
                                    <div class="text-success mb-2">
                                        <small>
                                            Dikumpulkan pada:<br>
                                            <?php echo date('d M Y H:i', strtotime($assignment['submitted_at'])); ?>
                                        </small>
                                    </div>
                                    <?php if ($assignment['grade'] !== null): ?>
                                    <div class="text-warning mb-2">
                                        <i class="bi bi-star-fill"></i> Nilai: <?php echo $assignment['grade']; ?>
                                    </div>
                                    <?php else: ?>
                                    <div class="text-muted mb-2">
                                        <i class="bi bi-hourglass-split"></i> Menunggu penilaian
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <?php else: ?>
                                <div class="text-danger mb-2">
                                    <?php if (strtotime($assignment['due_date']) < time()): ?>
                                    <i class="bi bi-exclamation-triangle-fill"></i> Batas waktu telah berakhir
                                    <?php else: ?>
                                    <i class="bi bi-exclamation-circle-fill"></i> Belum dikumpulkan
                                    <br>
                                    <small>
                                        Sisa waktu:
                                        <?php 
                                                        $remaining = strtotime($assignment['due_date']) - time();
                                                        $days = floor($remaining / (60 * 60 * 24));
                                                        $hours = floor(($remaining % (60 * 60 * 24)) / (60 * 60));
                                                        if ($days > 0) {
                                                            echo $days . " hari " . $hours . " jam";
                                                        } else {
                                                            echo $hours . " jam";
                                                        }
                                                    ?>
                                    </small>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                                <a href="submit_assignment.php?id=<?php echo $assignment['id']; ?>"
                                    class="btn btn-<?php echo $assignment['is_submitted'] ? 'success' : 'primary'; ?> btn-sm">
                                    <?php if ($assignment['is_submitted']): ?>
                                    <i class="bi bi-eye"></i> Lihat Tugas
                                    <?php else: ?>
                                    <i class="bi bi-upload"></i> Kumpulkan Tugas
                                    <?php endif; ?>
                                </a>
                                <?php else: ?>
                                <a href="view_submissions.php?id=<?php echo $assignment['id']; ?>"
                                    class="btn btn-primary btn-sm">
                                    <i class="bi bi-eye"></i> Lihat Pengumpulan
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>

        <h3>Pengumuman</h3>
        <div class="list-group">
            <?php while ($announcement = mysqli_fetch_assoc($announcements_result)): ?>
            <div class="list-group-item">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1"><?php echo $announcement['title']; ?></h5>
                        <p class="mb-1"><?php echo $announcement['content']; ?></p>
                        <small>
                            <i class="bi bi-person"></i> <?php echo $announcement['author_name']; ?> |
                            <i class="bi bi-clock"></i>
                            <?php echo date('d M Y H:i', strtotime($announcement['created_at'])); ?>
                        </small>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- Modal Buat Tugas -->
    <?php if ($role == 'teacher'): ?>
    <div class="modal fade" id="createAssignmentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Buat Tugas Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="create_assignment.php" method="POST">
                        <input type="hidden" name="class_id" value="<?php echo $class_id; ?>">
                        <div class="mb-3">
                            <label for="title" class="form-label">Judul Tugas</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="description" name="description" rows="3"
                                required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="due_date" class="form-label">Batas Waktu</label>
                            <input type="datetime-local" class="form-control" id="due_date" name="due_date" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Buat Tugas</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Modal Buat Pengumuman -->
    <div class="modal fade" id="createAnnouncementModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Buat Pengumuman</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="create_announcement.php" method="POST">
                        <input type="hidden" name="class_id" value="<?php echo $class_id; ?>">
                        <div class="mb-3">
                            <label for="announcement_title" class="form-label">Judul Pengumuman</label>
                            <input type="text" class="form-control" id="announcement_title" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="announcement_content" class="form-label">Isi Pengumuman</label>
                            <textarea class="form-control" id="announcement_content" name="content" rows="3"
                                required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Buat Pengumuman</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>