<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\PublicCourse;
use App\Models\Database;
use App\Models\Session;

Session::start();

// Pagination settings
$itemsPerPage = 9; // Show 9 courses per page (3x3 grid)
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$currentPage = max(1, $currentPage); // Ensure page is at least 1

include 'header.php';
?>

<!-- Header Start -->
<style>
    .course-card {
        transition: transform 0.3s ease;
        height: 100%;
    }
    .course-card:hover {
        transform: translateY(-5px);
    }
    .course-image {
        height: 200px;
        object-fit: cover;
        width: 100%;
    }
    .course-title {
        color: #2C3E50;
        text-decoration: none;
        display: block;
        min-height: 48px;
    }
    .course-title:hover {
        color: #0056b3;
        text-decoration: none;
    }
    .course-stats {
        font-size: 0.9rem;
    }
    .pagination .page-link {
        color: #2C3E50;
    }
    .pagination .active .page-link {
        background-color: #2C3E50;
        border-color: #2C3E50;
    }
</style>

<div class="jumbotron jumbotron-fluid page-header position-relative overlay-bottom" style="margin-bottom: 90px;">
    <div class="container text-center py-5">
        <h1 class="text-white display-1">Courses</h1>
        <div class="d-inline-flex text-white mb-5">
            <p class="m-0 text-uppercase"><a class="text-white" href="index.php">Home</a></p>
            <i class="fa fa-angle-double-right pt-1 px-3"></i>
            <p class="m-0 text-uppercase">Courses</p>
        </div>
    </div>
</div>
<!-- Header End -->

<!-- Courses Start -->
<div class="container-fluid py-5">
    <div class="container py-5">
        <div class="row mx-0 justify-content-center">
            <div class="col-lg-8">
                <div class="section-title text-center position-relative mb-5">
                    <h6 class="d-inline-block position-relative text-secondary text-uppercase pb-2">Our Courses</h6>
                    <h1 class="display-4">Checkout All Our Courses</h1>
                </div>
            </div>
        </div>
        <?php
        try {
            $courseModel = new PublicCourse();
            $courses = $courseModel->getApprovedCoursesWithPagination($currentPage, $itemsPerPage);
            $totalCourses = $courseModel->getTotalApprovedCourses();
            $totalPages = ceil($totalCourses / $itemsPerPage);

            if (empty($courses)) {
                ?>
                <div class="text-center">
                    <h3>No courses available</h3>
                    <p class="text-muted">Check back later for new courses!</p>
                </div>
                <?php
            } else {
                ?>
                <div class="row">
                    <?php foreach ($courses as $course) {
                        $thumbnail = $course->thumbnail ?? 'img/courses-1.jpg';
                        ?>
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="course-card rounded overflow-hidden shadow">
                                <img class="course-image" src="../<?php echo htmlspecialchars($thumbnail); ?>" alt="<?php echo htmlspecialchars($course->title); ?>">
                                <div class="bg-white p-4">
                                    <div class="d-flex justify-content-between mb-3 course-stats">
                                        <small class="m-0"><i class="fa fa-users text-primary mr-2"></i><?php echo $course->enrollment_count; ?> Students</small>
                                        <small class="m-0"><i class="fa fa-dollar-sign text-primary mr-2"></i><?php echo number_format($course->price, 2); ?></small>
                                    </div>
                                    <a class="h5 course-title mb-3" href="course-detail.php?id=<?php echo $course->id; ?>"><?php echo htmlspecialchars($course->title); ?></a>
                                    <div class="border-top mt-3 pt-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted"><i class="fa fa-user text-primary mr-2"></i><?php echo htmlspecialchars($course->teacher_name); ?></small>
                                            <a href="course-detail.php?id=<?php echo $course->id; ?>" class="btn btn-primary btn-sm">Learn More</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1) { ?>
                    <div class="row mt-4">
                        <div class="col-12">
                            <nav aria-label="Course pagination">
                                <ul class="pagination justify-content-center">
                                    <?php if ($currentPage > 1) { ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo ($currentPage - 1); ?>" aria-label="Previous">
                                                <span aria-hidden="true">&laquo;</span>
                                            </a>
                                        </li>
                                    <?php } ?>

                                    <?php
                                    $startPage = max(1, min($currentPage - 2, $totalPages - 4));
                                    $endPage = min($totalPages, max(5, $currentPage + 2));

                                    for ($i = $startPage; $i <= $endPage; $i++) {
                                        ?>
                                        <li class="page-item <?php echo ($i == $currentPage) ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php } ?>

                                    <?php if ($currentPage < $totalPages) { ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo ($currentPage + 1); ?>" aria-label="Next">
                                                <span aria-hidden="true">&raquo;</span>
                                            </a>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </nav>
                        </div>
                    </div>
                <?php } ?>
            <?php } ?>

        <?php } catch (\Exception $e) {
            error_log('Error in courses.php: ' . $e->getMessage());
            ?>
            <div class="text-center">
                <h3>Error loading courses</h3>
                <p class="text-danger">Please try again later.</p>
            </div>
        <?php } ?>
    </div>
</div>
<!-- Courses End -->

<?php include 'footer.php'; ?> 