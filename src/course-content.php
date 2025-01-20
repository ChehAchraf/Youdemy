<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Session;
use App\Models\Student;
use App\Models\PublicCourse;

Session::start();


if (!Session::isLoggedIn() || Session::get('role') !== 'student') {
    header('Location: login.php');
    exit();
}


$courseId = $_GET['id'] ?? null;

if (!$courseId) {
    header('Location: my-courses.php');
    exit();
}

try {
    $student = new Student();
    

    if (!$student->isEnrolledInCourse($courseId)) {
        header('Location: my-courses.php?error=not_enrolled');
        exit();
    }
    

    $courseModel = new PublicCourse();
    $course = $courseModel->displayCourse($courseId);
    
    if (!$course) {
        header('Location: my-courses.php');
        exit();
    }
    
    include 'header.php';
?>

    <!-- Header Start -->
    <div class="jumbotron jumbotron-fluid page-header position-relative overlay-bottom" style="margin-bottom: 90px;">
        <div class="container text-center py-5">
            <h1 class="text-white display-4"><?php echo htmlspecialchars($course->title); ?></h1>
            <div class="d-inline-flex text-white mb-5">
                <p class="m-0 text-uppercase"><a class="text-white" href="index.php">Home</a></p>
                <i class="fa fa-angle-double-right pt-1 px-3"></i>
                <p class="m-0 text-uppercase"><a class="text-white" href="my-courses.php">My Courses</a></p>
                <i class="fa fa-angle-double-right pt-1 px-3"></i>
                <p class="m-0 text-uppercase">Course Content</p>
            </div>
        </div>
    </div>
    <!-- Header End -->

    <!-- Content Start -->
    <div class="container-fluid py-5">
        <div class="container py-5">
            <div class="row">
                <!-- Course Content -->
                <div class="col-lg-8">
                    <div class="mb-5">
                        <h2 class="mb-4">Course Content</h2>
                        
                        <!-- Course Media Content -->
                        <?php if ($course->media): ?>
                            <?php
                            $mediaPath = $course->media;
                            $fileExtension = strtolower(pathinfo($mediaPath, PATHINFO_EXTENSION));
                            $videoExtensions = ['mp4', 'webm', 'ogg'];
                            $documentExtensions = ['pdf', 'doc', 'docx', 'ppt', 'pptx'];
                            $archiveExtensions = ['zip', 'rar'];
                            
                            if (in_array($fileExtension, $videoExtensions)):
                            ?>
                                <!-- Video Display -->
                                <div class="card mb-4">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="mb-0">
                                            <i class="fas fa-play-circle mr-2"></i>
                                            Course Video
                                        </h5>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="embed-responsive embed-responsive-16by9">
                                            <video class="embed-responsive-item" controls controlsList="nodownload">
                                                <source src="../<?php echo htmlspecialchars($mediaPath); ?>" type="video/<?php echo $fileExtension; ?>">
                                                Your browser does not support the video tag.
                                            </video>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <!-- File Download Section -->
                                <div class="card mb-4">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="mb-0">
                                            <?php
                                            $icon = in_array($fileExtension, $documentExtensions) ? 'fa-file-alt' : 'fa-file-archive';
                                            ?>
                                            <i class="fas <?php echo $icon; ?> mr-2"></i>
                                            Course Material
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-2">Available Course Material</h6>
                                                <p class="mb-0 text-muted">
                                                    File type: <span class="badge badge-info"><?php echo strtoupper($fileExtension); ?></span>
                                                </p>
                                            </div>
                                            <div class="ml-auto">
                                                <a href="../<?php echo htmlspecialchars($mediaPath); ?>" 
                                                   class="btn btn-primary" 
                                                   download>
                                                    <i class="fas fa-download mr-2"></i>
                                                    Download
                                                </a>
                                                <?php if (in_array($fileExtension, ['pdf'])): ?>
                                                    <a href="../<?php echo htmlspecialchars($mediaPath); ?>" 
                                                       class="btn btn-secondary ml-2" 
                                                       target="_blank">
                                                        <i class="fas fa-eye mr-2"></i>
                                                        View
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Course Sidebar -->
                <div class="col-lg-4">
                    <div class="bg-primary mb-5 py-3">
                        <h3 class="text-white py-3 px-4 m-0">Course Information</h3>
                        <div class="d-flex justify-content-between border-bottom px-4">
                            <h6 class="text-white my-3">Instructor</h6>
                            <h6 class="text-white my-3"><?php echo htmlspecialchars($course->teacher_name); ?></h6>
                        </div>
                        <div class="d-flex justify-content-between border-bottom px-4">
                            <h6 class="text-white my-3">Category</h6>
                            <h6 class="text-white my-3"><?php echo htmlspecialchars($course->category_name); ?></h6>
                        </div>
                        <?php
                        $enrollmentDate = $student->getEnrollmentDate($courseId);
                        if ($enrollmentDate): 
                        ?>
                        <div class="d-flex justify-content-between px-4">
                            <h6 class="text-white my-3">Enrolled Date</h6>
                            <h6 class="text-white my-3"><?php echo date('M d, Y', strtotime($enrollmentDate)); ?></h6>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Content End -->

<?php 
} catch (Exception $e) {
    error_log('Error in course-content.php: ' . $e->getMessage());
    header('Location: my-courses.php');
    exit();
}

include 'footer.php'; 
?> 