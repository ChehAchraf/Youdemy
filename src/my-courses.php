<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Session;
use App\Models\Database;

Session::start();

// Check if user is logged in and is a student
if (!Session::isLoggedIn() || Session::get('role') !== 'student') {
    header('Location: login.php');
    exit();
}

try {
    $db = Database::getInstance()->getConnection();

    // Get all enrolled courses for the student
    $stmt = $db->prepare("
        SELECT c.*, 
               cat.name as category_name,
               CONCAT(u.firstName, ' ', u.lastName) as teacher_name,
               e.enrollDate
        FROM courses c
        INNER JOIN enrollments e ON c.id = e.courseId
        LEFT JOIN categories cat ON c.categoryId = cat.id
        LEFT JOIN users u ON c.teacherId = u.id
        WHERE e.studentId = :studentId
        AND c.isApproved = 1
        AND c.deleted_at IS NULL
        ORDER BY e.enrollDate DESC
    ");

    $stmt->execute([':studentId' => Session::get('user_id')]);
    $enrolledCourses = $stmt->fetchAll(PDO::FETCH_OBJ);

    include 'header.php';
?>

    <!-- Header Start -->
    <div class="jumbotron jumbotron-fluid page-header position-relative overlay-bottom" style="margin-bottom: 90px;">
        <div class="container text-center py-5">
            <h1 class="text-white display-4">My Courses</h1>
            <div class="d-inline-flex text-white mb-5">
                <p class="m-0 text-uppercase"><a class="text-white" href="index.php">Home</a></p>
                <i class="fa fa-angle-double-right pt-1 px-3"></i>
                <p class="m-0 text-uppercase">My Courses</p>
            </div>
        </div>
    </div>
    <!-- Header End -->

    <!-- Courses Start -->
    <div class="container-fluid py-5">
        <div class="container py-5">
            <div class="row">
                <?php if (empty($enrolledCourses)): ?>
                    <div class="col-12 text-center">
                        <h3>You haven't enrolled in any courses yet.</h3>
                        <a href="courses.php" class="btn btn-primary mt-3">Browse Courses</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($enrolledCourses as $course): ?>
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="rounded overflow-hidden  mb-2 shadow-sm h-100">
                                <?php if ($course->thumbnail): ?>
                                    <img class="img-fluid" src="../<?php echo htmlspecialchars($course->thumbnail); ?>" alt="Course Thumbnail">
                                <?php else: ?>
                                    <img class="img-fluid" src="img/course-1.jpg" alt="Default Thumbnail">
                                <?php endif; ?>
                                <div class="bg-white p-4 ">
                                    <div class="d-flex justify-content-between mb-3">
                                        <small class="m-0">
                                            <i class="fa fa-users text-primary mr-2"></i>
                                            <?php echo $course->enrollmentCount ?? 0; ?> Students
                                        </small>
                                        <small class="m-0">
                                            <i class="far fa-clock text-primary mr-2"></i>
                                            Enrolled <?php echo date('M d, Y', strtotime($course->enrollDate)); ?>
                                        </small>
                                    </div>
                                    <h5 class="card-title"><?php echo htmlspecialchars($course->title); ?></h5>
                                    <div class="border-top mt-4 pt-4">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <h6 class="m-0"><i class="fa fa-user text-primary mr-2"></i><?php echo htmlspecialchars($course->teacher_name); ?></h6>
                                            <a href="course-content.php?id=<?php echo $course->id; ?>" 
                                               class="btn btn-primary py-2 px-4">
                                                Access Course
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <!-- Courses End -->

<?php
    include 'footer.php';
} catch (\Exception $e) {
    error_log('Error in my-courses.php: ' . $e->getMessage());
    header('Location: index.php');
    exit();
}
?> 