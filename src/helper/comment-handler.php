<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Models\Session;
use App\Models\Student;

Session::start();

header('Content-Type: application/json');

if (!Session::isLoggedIn() || Session::get('role') !== 'student') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit();
}

try {
    $student = new Student();
    $action = $_POST['action'] ?? '';
    $courseId = isset($_POST['courseId']) ? (int)$_POST['courseId'] : null;
    
    switch ($action) {
        case 'get':
            if (!$courseId) {
                throw new \Exception('Course ID is required');
            }
            $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
            $reviews = Student::getCourseReviews($courseId, $page);
            $total = Student::getTotalReviews($courseId);
            
            ob_start();
            foreach ($reviews as $review) {
                ?>
                <div class="review-item mb-4" data-review-id="<?php echo $review->id; ?>">
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-user-circle fa-2x text-secondary mr-3"></i>
                        <div>
                            <h6 class="mb-0"><?php echo htmlspecialchars($review->student_name); ?></h6>
                            <div class="text-muted">
                                <small>
                                    <?php echo date('M d, Y', strtotime($review->createdAt)); ?>
                                </small>
                            </div>
                        </div>
                    </div>
                    <p class="review-content mb-0"><?php echo nl2br(htmlspecialchars($review->content)); ?></p>
                    <?php if ($review->userId == Session::get('user_id')): ?>
                        <div class="mt-2">
                            <button class="btn btn-sm btn-outline-primary" onclick="editReview(<?php echo $review->id; ?>)">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteReview(<?php echo $review->id; ?>)">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
                <?php
            }
            $html = ob_get_clean();
            
            echo json_encode([
                'success' => true,
                'html' => $html,
                'total' => $total
            ]);
            break;
            
        case 'add':
            $content = $_POST['content'] ?? '';
            
            if (!$courseId || !$content) {
                throw new \Exception('Missing required fields');
            }
            
            $student->addReview($courseId, $content);
            echo json_encode([
                'success' => true,
                'message' => 'Comment added successfully'
            ]);
            break;
            
        case 'update':
            $reviewId = isset($_POST['reviewId']) ? (int)$_POST['reviewId'] : null;
            $content = $_POST['content'] ?? '';
            
            if (!$reviewId || !$content) {
                throw new \Exception('Missing required fields');
            }
            
            $student->updateReview($reviewId, $content);
            echo json_encode([
                'success' => true,
                'message' => 'Comment updated successfully'
            ]);
            break;
            
        case 'delete':
            $reviewId = isset($_POST['reviewId']) ? (int)$_POST['reviewId'] : null;
            
            if (!$reviewId) {
                throw new \Exception('Comment ID is required');
            }
            
            $student->deleteReview($reviewId);
            echo json_encode([
                'success' => true,
                'message' => 'Comment deleted successfully'
            ]);
            break;
            
        default:
            throw new \Exception('Invalid action');
    }

} catch (\Exception $e) {
    error_log('Error in comment-handler.php: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}