<?php
// --- 1. CẤU HÌNH KẾT NỐI DATABASE QUA BIẾN MÔI TRƯỜNG ---
$host     = getenv('DB_HOST') ?: 'localhost'; 
$port     = getenv('DB_PORT') ?: '4000';
$dbname   = getenv('DB_NAME') ?: 'test';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS') ?: '';

// CẤU HÌNH SSL CHUẨN XÁC CHO TIDB SERVERLESS
$options = [
    PDO::MYSQL_ATTR_SSL_CA => '/etc/ssl/certs/isrgrootx1.pem'
];

try {
    // Khởi tạo kết nối an toàn với $options
    $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password, $options);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Kết nối cơ sở dữ liệu thất bại: " . $e->getMessage());
}

$message = '';
$status = '';

// --- 2. XỬ LÝ CHỨC NĂNG CRUD ---

// Chức năng: THÊM SINH VIÊN
if (isset($_POST['btn_create'])) {
    $fullname = $_POST['fullname'];
    $email    = $_POST['email'];
    $phone    = $_POST['phone'];
    $course   = $_POST['course'];

    if (!empty($fullname) && !empty($email)) {
        $sql = "INSERT INTO students (fullname, email, phone, course) VALUES (:fullname, :email, :phone, :course)";
        $stmt = $conn->prepare($sql);
        if ($stmt->execute([':fullname' => $fullname, ':email' => $email, ':phone' => $phone, ':course' => $course])) {
            $message = "Thêm sinh viên thành công!";
            $status = "success";
        }
    } else {
        $message = "Vui lòng điền đầy đủ Họ tên và Email!";
        $status = "danger";
    }
}

// Chức năng: CẬP NHẬT SINH VIÊN
if (isset($_POST['btn_update'])) {
    $id       = $_POST['id'];
    $fullname = $_POST['fullname'];
    $email    = $_POST['email'];
    $phone    = $_POST['phone'];
    $course   = $_POST['course'];

    if (!empty($id) && !empty($fullname) && !empty($email)) {
        $sql = "UPDATE students SET fullname = :fullname, email = :email, phone = :phone, course = :course WHERE id = :id";
        $stmt = $conn->prepare($sql);
        if ($stmt->execute([':fullname' => $fullname, ':email' => $email, ':phone' => $phone, ':course' => $course, ':id' => $id])) {
            $message = "Cập nhật thông tin thành công!";
            $status = "success";
        }
    }
}

// Chức năng: XÓA SINH VIÊN
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    $sql = "DELETE FROM students WHERE id = :id";
    $stmt = $conn->prepare($sql);
    if ($stmt->execute([':id' => $id])) {
        header("Location: index.php?msg=deleted");
        exit();
    }
}

if (isset($_GET['msg']) && $_GET['msg'] == 'deleted') {
    $message = "Đã xóa sinh viên thành công!";
    $status = "success";
}

// Lấy thông tin sinh viên cần sửa (nếu có hành động bấm nút Sửa)
$edit_student = null;
if (isset($_GET['edit_id'])) {
    $id = $_GET['edit_id'];
    $sql = "SELECT * FROM students WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id' => $id]);
    $edit_student = $stmt->fetch(PDO::FETCH_ASSOC);
}

// LẤY DANH SÁCH TẤT CẢ SINH VIÊN ĐỂ HIỂN THỊ
$sql_select = "SELECT * FROM students ORDER BY id DESC";
$stmt_select = $conn->prepare($sql_select);
$stmt_select->execute();
$students = $stmt_select->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ứng dụng Quản lý Sinh viên - CRUD PHP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container my-5">
    <div class="row mb-4">
        <div class="col-12 text-center">
            <h2 class="fw-bold text-primary">HỆ THỐNG QUẢN LÝ SINH VIÊN</h2>
            <p class="text-muted">Ứng dụng mẫu PHP + MySQL triển khai trên PaaS/Hosting</p>
        </div>
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?= $status ?> alert-dismissible fade show" role="alert">
            <?= $message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white fw-bold">
                    <?= $edit_student ? 'Cập Nhật Thông Tin' : 'Thêm Sinh Viên Mới' ?>
                </div>
                <div class="card-body">
                    <form action="index.php" method="POST">
                        <?php if ($edit_student): ?>
                            <input type="hidden" name="id" value="<?= $edit_student['id'] ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label">Họ và Tên <span class="text-danger">*</span></label>
                            <input type="text" name="fullname" class="form-control" required value="<?= $edit_student ? htmlspecialchars($edit_student['fullname']) : '' ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" required value="<?= $edit_student ? htmlspecialchars($edit_student['email']) : '' ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Số điện thoại</label>
                            <input type="text" name="phone" class="form-control" value="<?= $edit_student ? htmlspecialchars($edit_student['phone']) : '' ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Khóa học</label>
                            <input type="text" name="course" class="form-control" value="<?= $edit_student ? htmlspecialchars($edit_student['course']) : '' ?>">
                        </div>

                        <div class="d-grid gap-2">
                            <?php if ($edit_student): ?>
                                <button type="submit" name="btn_update" class="btn btn-warning fw-bold">Lưu Thay Đổi</button>
                                <a href="index.php" class="btn btn-secondary text-white">Hủy bỏ</a>
                            <?php else: ?>
                                <button type="submit" name="btn_create" class="btn btn-success fw-bold">Thêm Mới</button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white fw-bold">
                    Danh Sách Sinh Viên Hiện Tại
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0 align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th class="ps-3">ID</th>
                                    <th>Họ và Tên</th>
                                    <th>Email</th>
                                    <th>Điện thoại</th>
                                    <th>Khóa học</th>
                                    <th class="text-center">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($students) > 0): ?>
                                    <?php foreach ($students as $row): ?>
                                        <tr>
                                            <td class="ps-3"><?= $row['id'] ?></td>
                                            <td class="fw-bold"><?= htmlspecialchars($row['fullname']) ?></td>
                                            <td><?= htmlspecialchars($row['email']) ?></td>
                                            <td><?= htmlspecialchars($row['phone']) ?></td>
                                            <td><span class="badge bg-secondary"><?= htmlspecialchars($row['course']) ?></span></td>
                                            <td class="text-center">
                                                <a href="index.php?edit_id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-warning me-1">Sửa</a>
                                                <a href="index.php?delete_id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa sinh viên này?')">Xóa</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted p-4">Chưa có dữ liệu sinh viên nào trong hệ thống.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>