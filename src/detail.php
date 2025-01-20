<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Session;
use App\Models\PublicCourse;
use App\Models\Student;

Session::start();

// Get course ID from URL
$courseId = $_GET['id'] ?? null;

if (!$courseId) {
    header('Location: courses.php');
    exit();
}

try {
    $courseModel = new PublicCourse();
    $course = $courseModel->displayCourse($courseId);
    
    if (!$course) {
        header('Location: courses.php');
        exit();
    }
    
    include 'header.php';
?>

    <!-- Header Start -->
    <div class="jumbotron jumbotron-fluid page-header position-relative overlay-bottom" style="margin-bottom: 90px;">
        <div class="container text-center py-5">
            <h1 class="text-white display-1">Course Detail</h1>
            <div class="d-inline-flex text-white mb-5">
                <p class="m-0 text-uppercase"><a class="text-white" href="index.php">Home</a></p>
                <i class="fa fa-angle-double-right pt-1 px-3"></i>
                <p class="m-0 text-uppercase"><a class="text-white" href="courses.php">Courses</a></p>
                <i class="fa fa-angle-double-right pt-1 px-3"></i>
                <p class="m-0 text-uppercase">Course Detail</p>
            </div>
        </div>
    </div>
    <!-- Header End -->

    <!-- Detail Start -->
    <div class="container-fluid py-5">
        <div class="container py-5">
            <div class="row">
                <div class="col-lg-8">
                    <div class="mb-5">
                        <div class="section-title position-relative mb-5">
                            <h6 class="d-inline-block position-relative text-secondary text-uppercase pb-2">Course Detail</h6>
                            <h1 class="display-4"><?php echo htmlspecialchars($course->title); ?></h1>
                        </div>
                        <img class="img-fluid rounded w-100 mb-4" src="../<?php echo $course->thumbnail ?? 'img/courses-1.jpg'; ?>" alt="<?php echo htmlspecialchars($course->title); ?>">
                        <p><?php echo nl2br($course->description); ?></p>
                    </div>

                    <h2 class="mb-3">Student Comments</h2>
                    <div class="mb-5">
                        <?php if (Session::isLoggedIn() && Session::get('role') === 'student'): ?>
                            <?php
                            $student = new Student();
                            $isEnrolled = $student->isEnrolledInCourse($course->id);
                            $existingReview = $student->getStudentReview($course->id);
                            
                            if (!$isEnrolled): ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    You need to be enrolled in this course to leave a comment.
                                </div>
                            <?php elseif (!$existingReview): ?>
                                <div class="bg-light rounded p-4 mb-4">
                                    <h5>Write a Comment</h5>
                                    <form id="reviewForm" onsubmit="submitReview(event)">
                                        <input type="hidden" name="courseId" value="<?php echo $course->id; ?>">
                                        <input type="hidden" name="action" value="add">
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Your Comment</label>
                                            <textarea class="form-control" name="content" rows="4" required></textarea>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-primary">Submit Comment</button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <div id="reviewsContainer">
                            <!-- Comments will be loaded here -->
                        </div>
                        
                        <div id="reviewsPagination" class="mt-4">
                            <!-- Pagination will be added here -->
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 mt-5 mt-lg-0">
                    <div class="bg-primary mb-5 py-3">
                        <h3 class="text-white py-3 px-4 m-0">Course Features</h3>
                        <div class="d-flex justify-content-between border-bottom px-4">
                            <h6 class="text-white my-3">Instructor</h6>
                            <h6 class="text-white my-3"><?php echo htmlspecialchars($course->teacher_name); ?></h6>
                        </div>
                        <div class="d-flex justify-content-between border-bottom px-4">
                            <h6 class="text-white my-3">Rating</h6>
                            <h6 class="text-white my-3"><?php echo number_format($course->rating ?? 0, 1); ?> 
                                <small>(<?php echo $course->rating_count ?? 0; ?>)</small>
                            </h6>
                        </div>
                        <div class="d-flex justify-content-between border-bottom px-4">
                            <h6 class="text-white my-3">Enrolled Students</h6>
                            <h6 class="text-white my-3"><?php echo $course->enrollment_count ?? 0; ?></h6>
                        </div>
                        <div class="d-flex justify-content-between px-4">
                            <h6 class="text-white my-3">Category</h6>
                            <h6 class="text-white my-3"><?php echo htmlspecialchars($course->category_name); ?></h6>
                        </div>
                        <h5 class="text-white py-3 px-4 m-0">Course Price: $<?php echo number_format($course->price, 2); ?></h5>
                        <div class="py-3 px-4">
                            <?php if (Session::isLoggedIn() && Session::get('role') === 'student'): ?>
                                <button class="btn btn-block btn-secondary py-3 px-5" 
                                        hx-post="helper/enroll-course.php"
                                        hx-vals='{"courseId": <?php echo $course->id; ?>}'
                                        hx-swap="none">
                                    Enroll Now
                                </button>
                                <script>
                                document.body.addEventListener('htmx:afterRequest', function(evt) {
                                    if (evt.detail.target.classList.contains('btn-secondary')) {
                                        const response = JSON.parse(evt.detail.xhr.response);
                                        
                                        if (response.success) {
                                            Swal.fire({
                                                title: 'Success!',
                                                text: response.message,
                                                icon: 'success',
                                                showConfirmButton: false,
                                                timer: 1500
                                            }).then(() => {
                                                if (response.redirect) {
                                                    window.location.href = response.redirect;
                                                }
                                            });
                                        } else {
                                            Swal.fire({
                                                title: 'Error!',
                                                text: response.message,
                                                icon: 'error',
                                                confirmButtonColor: '#d33'
                                            });
                                        }
                                    }
                                });
                                </script>
                            <?php elseif (!Session::isLoggedIn()): ?>
                                <a href="login.php" class="btn btn-block btn-secondary py-3 px-5">Login to Enroll</a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mb-5">
                        <h2 class="mb-3">Categories</h2>
                        <ul class="list-group list-group-flush">
                            <?php
                            $categories = $courseModel->getAllCategories();
                            foreach ($categories as $category) {
                            ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <a href="courses.php?category=<?php echo $category->id; ?>" class="text-decoration-none h6 m-0">
                                    <?php echo htmlspecialchars($category->name); ?>
                                </a>
                                <span class="badge badge-primary badge-pill"><?php echo $category->course_count; ?></span>
                            </li>
                            <?php } ?>
                        </ul>
                    </div>

                    <div class="mb-5">
                        <h2 class="mb-4">Recent Courses</h2>
                        <?php
                        $recentCourses = $courseModel->getRecentCourses(4);
                        foreach ($recentCourses as $recentCourse) {
                            $thumbnail = $recentCourse->thumbnail ?? 'img/courses-80x80.jpg';
                        ?>
                        <a class="d-flex align-items-center text-decoration-none mb-4" href="detail.php?id=<?php echo $recentCourse->id; ?>">
                            <img class="img-fluid rounded" src="../<?php echo $thumbnail; ?>" alt="" style="width: 80px; height: 80px; object-fit: cover;">
                            <div class="pl-3">
                                <h6><?php echo htmlspecialchars($recentCourse->title); ?></h6>
                                <div class="d-flex">
                                    <small class="text-body mr-3">
                                        <i class="fa fa-user text-primary mr-2"></i><?php echo htmlspecialchars($recentCourse->teacher_name); ?>
                                    </small>
                                    <small class="text-body">
                                        <i class="fa fa-star text-primary mr-2"></i><?php echo number_format($recentCourse->rating ?? 0, 1); ?> 
                                        (<?php echo $recentCourse->rating_count ?? 0; ?>)
                                    </small>
                                </div>
                            </div>
                        </a>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Detail End -->

    <style>
    .rating {
        display: flex;
        flex-direction: row-reverse;
        gap: 0.3rem;
        font-size: 1.5rem;
        justify-content: flex-end;
    }

    .rating input {
        display: none;
    }

    .rating label {
        cursor: pointer;
        color: #ddd;
        transition: color 0.2s;
    }

    .rating input:checked ~ label,
    .rating label:hover,
    .rating label:hover ~ label {
        color: #ffc107;
    }

    .rating label:hover,
    .rating label:hover ~ label {
        color: #ffdb70;
    }

    .review-item {
        border-bottom: 1px solid #dee2e6;
        padding-bottom: 1rem;
    }

    .review-item:last-child {
        border-bottom: none;
    }
    </style>

    <script>
    // Load comments when page loads
    document.addEventListener('DOMContentLoaded', function() {
        loadReviews(1);
    });

    function loadReviews(page) {
        const courseId = <?php echo $course->id; ?>;
        fetch(`helper/comment-handler.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=get&courseId=${courseId}&page=${page}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('reviewsContainer').innerHTML = data.html;
                updatePagination(page, Math.ceil(data.total / 5));
            }
        })
        .catch(error => console.error('Error loading comments:', error));
    }

    function submitReview(event) {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);
        
        fetch('helper/comment-handler.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                form.reset();
                loadReviews(1); // Reload comments after adding new one
                // Hide the comment form after successful submission
                form.closest('.bg-light').style.display = 'none';
            }
            // Show success/error message using SweetAlert
            Swal.fire({
                icon: data.success ? 'success' : 'error',
                title: data.success ? 'Success' : 'Error',
                text: data.message
            });
        })
        .catch(error => {
            console.error('Error submitting comment:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to submit comment. Please try again.'
            });
        });
    }

    function editReview(reviewId) {
        // Get the review content
        const reviewElement = document.querySelector(`[data-review-id="${reviewId}"]`);
        const content = reviewElement.querySelector('.review-content').textContent;

        // Show edit form using SweetAlert
        Swal.fire({
            title: 'Edit Comment',
            input: 'textarea',
            inputValue: content,
            showCancelButton: true,
            confirmButtonText: 'Update',
            showLoaderOnConfirm: true,
            preConfirm: (newContent) => {
                if (!newContent.trim()) {
                    Swal.showValidationMessage('Comment cannot be empty');
                    return false;
                }
                const formData = new FormData();
                formData.append('action', 'update');
                formData.append('reviewId', reviewId);
                formData.append('content', newContent);
                formData.append('courseId', <?php echo $course->id; ?>);

                return fetch('helper/comment-handler.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) throw new Error(data.message);
                    return data;
                });
            }
        }).then((result) => {
            if (result.isConfirmed) {
                loadReviews(1); // Reload comments after update
                Swal.fire('Success', 'Comment updated successfully', 'success');
            }
        });
    }

    function deleteReview(reviewId) {
        Swal.fire({
            title: 'Are you sure?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('reviewId', reviewId);
                formData.append('courseId', <?php echo $course->id; ?>);
                
                fetch('helper/comment-handler.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadReviews(1); // Reload comments after deletion
                    }
                    Swal.fire({
                        icon: data.success ? 'success' : 'error',
                        title: data.success ? 'Success' : 'Error',
                        text: data.message
                    });
                })
                .catch(error => {
                    console.error('Error deleting comment:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to delete comment. Please try again.'
                    });
                });
            }
        });
    }

    function updatePagination(currentPage, totalPages) {
        const container = document.getElementById('reviewsPagination');
        if (totalPages <= 1) {
            container.innerHTML = '';
            return;
        }
        
        let html = '<nav><ul class="pagination justify-content-center">';
        
        // Previous button
        html += `
            <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="loadReviews(${currentPage - 1}); return false;">Previous</a>
            </li>
        `;
        
        // Page numbers
        for (let i = 1; i <= totalPages; i++) {
            html += `
                <li class="page-item ${currentPage === i ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="loadReviews(${i}); return false;">${i}</a>
                </li>
            `;
        }
        
        // Next button
        html += `
            <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="loadReviews(${currentPage + 1}); return false;">Next</a>
            </li>
        `;
        
        html += '</ul></nav>';
        container.innerHTML = html;
    }
    </script>

<?php 
} catch (Exception $e) {
    error_log('Error in detail.php: ' . $e->getMessage());
    header('Location: courses.php');
    exit();
}

include 'footer.php'; 
?>