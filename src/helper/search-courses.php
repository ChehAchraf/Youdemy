<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Models\CourseSearch;

header('Content-Type: application/json');

try {
    $search = new CourseSearch();
    
    $query = $_GET['query'] ?? '';
    $categoryId = !empty($_GET['category']) ? (int)$_GET['category'] : null;
    $tags = !empty($_GET['tags']) ? explode(',', $_GET['tags']) : [];
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    
    $courses = $search->searchCourses($query, $categoryId, $tags, $page);
    $total = $search->getTotalSearchResults($query, $categoryId, $tags);
    
    ob_start();
    foreach ($courses as $course) {
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
                    <a class="h5 course-title mb-3" href="detail.php?id=<?php echo $course->id; ?>"><?php echo htmlspecialchars($course->title); ?></a>
                    <div class="border-top mt-3 pt-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted"><i class="fa fa-user text-primary mr-2"></i><?php echo htmlspecialchars($course->teacher_name); ?></small>
                            <a href="detail.php?id=<?php echo $course->id; ?>" class="btn btn-primary btn-sm">Learn More</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    $html = ob_get_clean();
    
    $itemsPerPage = 9;
    $totalPages = ceil($total / $itemsPerPage);
    
    echo json_encode([
        'success' => true,
        'html' => $html,
        'total' => $total,
        'currentPage' => $page,
        'totalPages' => $totalPages
    ]);

} catch (Exception $e) {
    error_log('Error in search-courses.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while searching courses'
    ]);
} 