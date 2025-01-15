<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Session;
use App\Models\TeacherCourse;
use App\Models\Database;

Session::start();

// Check if user is logged in and is a teacher
if (!Session::get('user_id') || Session::get('role') !== 'teacher') {
    header('Location: login.php');
    exit();
}

$courseModel = new TeacherCourse();
$courses = $courseModel->getAllCourses();

include 'header.php';
?>

<!-- Dashboard Start -->
<div class="container-fluid py-5">
    <div class="container">
        <div class="row">
            <!-- Sidebar/Tabs -->
            <div class="col-lg-3">
                <div class="nav flex-column nav-pills" role="tablist">
                    <a class="nav-link active" data-toggle="pill" href="#overview">
                        <i class="fas fa-chart-line mr-2"></i>Overview
                    </a>
                    <a class="nav-link" data-toggle="pill" href="#my-courses">
                        <i class="fas fa-book mr-2"></i>My Courses
                    </a>
                    <a class="nav-link" data-toggle="pill" href="#add-course">
                        <i class="fas fa-plus-circle mr-2"></i>Add Course
                    </a>
                    <a class="nav-link" data-toggle="pill" href="#students">
                        <i class="fas fa-users mr-2"></i>My Students
                    </a>
                    <a class="nav-link" data-toggle="pill" href="#earnings">
                        <i class="fas fa-dollar-sign mr-2"></i>Earnings
                    </a>
                </div>
            </div>

            <!-- Content -->
            <div class="col-lg-9">
                <div class="tab-content">
                    <!-- Overview Tab -->
                    <div class="tab-pane fade show active" id="overview">
                        <h3 class="mb-4">Dashboard Overview</h3>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="stat-card text-center">
                                    <i class="fas fa-book fa-3x mb-3 text-primary"></i>
                                    <div class="stat-number">12</div>
                                    <div class="stat-label">Active Courses</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="stat-card text-center">
                                    <i class="fas fa-users fa-3x mb-3 text-success"></i>
                                    <div class="stat-number">156</div>
                                    <div class="stat-label">Total Students</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="stat-card text-center">
                                    <i class="fas fa-dollar-sign fa-3x mb-3 text-info"></i>
                                    <div class="stat-number">$1,250</div>
                                    <div class="stat-label">Monthly Earnings</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- My Courses Tab -->
                    <div class="tab-pane fade" id="my-courses">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h3>My Courses</h3>
                            <button class="btn btn-primary" data-toggle="pill" href="#add-course">
                                <i class="fas fa-plus mr-2"></i>Add New Course
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Course Name</th>
                                        <th>Category</th>
                                        <th>Price</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $courseModel = new TeacherCourse();
                                    $courses = $courseModel->getAllCourses();

                                    if ($courses) {
                                        foreach($courses as $course) {
                                            $statusClass = match($course->status_label) {
                                                'Approved' => 'success',
                                                'Rejected' => 'danger',
                                                default => 'warning'
                                            };
                                                
                                            echo "<tr>
                                                <td>
                                                    <img src='{$course->thumbnail}' alt='thumbnail' class='img-thumbnail mr-2' style='width: 50px; height: 50px; object-fit: cover;'>
                                                    " . htmlspecialchars($course->title) . "
                                                </td>
                                                <td>" . htmlspecialchars($course->category_name) . "</td>
                                                <td>$" . number_format($course->price, 2) . "</td>
                                                <td><span class='badge badge-{$statusClass}'>{$course->status_label}</span></td>
                                                <td>
                                                    <button class='btn btn-sm btn-primary' onclick='editCourse({$course->id})'>
                                                        <i class='fas fa-edit'></i>
                                                    </button>
                                                    <button class='btn btn-sm btn-danger' onclick='deleteCourse({$course->id})'>
                                                        <i class='fas fa-trash'></i>
                                                    </button>
                                                </td>
                                            </tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='5' class='text-center'>No courses found</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Add Course Tab -->
                    <div class="tab-pane fade" id="add-course">
                        <h3 class="mb-4">Add New Course</h3>
                        <form hx-post="helper/add-course.php" 
                              hx-encoding="multipart/form-data"
                              hx-indicator="#loading"
                              hx-target="#add-course-response"
                              hx-swap="innerHTML">
                            <div id="loading" class="htmx-indicator">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="sr-only">Loading...</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Course Title</label>
                                <input type="text" name="title" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="description" class="form-control" rows="4" required></textarea>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>Category</label>
                                    <select name="categoryId" class="form-control" required>
                                        <option value="">Select Category</option>
                                        <?php
                                        $db = Database::getInstance()->getConnection();
                                        $stmt = $db->query("SELECT id, name FROM categories ORDER BY name");
                                        while ($category = $stmt->fetch(PDO::FETCH_OBJ)) {
                                            echo "<option value='" . $category->id . "'>" . htmlspecialchars($category->name) . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Price ($)</label>
                                    <input type="number" name="price" step="0.01" class="form-control" required>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>Thumbnail</label>
                                    <input type="file" name="thumbnail" class="form-control" accept="image/*" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>The course content</label>
                                    <input type="file" name="content" class="form-control" accept="video/*,.pdf" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Tags (comma separated)</label>
                                <input type="text" name="tags" class="form-control" placeholder="e.g., javascript, web development, programming">
                                <small class="form-text text-muted">Enter tags separated by commas. These help students find your course.</small>
                            </div>
                            <button type="submit" class="btn btn-primary">Create Course</button>
                        </form>
                        <div id="add-course-response">
                            
                        </div>
                    </div>

                    <!-- Students Tab -->
                    <div class="tab-pane fade" id="students">
                        <h3 class="mb-4">Enrolled Students</h3>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Student Name</th>
                                        <th>Course</th>
                                        <th>Enrolled Date</th>
                                        <th>Progress</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>John Doe</td>
                                        <td>Web Development Bootcamp</td>
                                        <td>2024-01-15</td>
                                        <td>
                                            <div class="progress">
                                                <div class="progress-bar" role="progressbar" style="width: 75%">75%</div>
                                            </div>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-info">View Details</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Earnings Tab -->
                    <div class="tab-pane fade" id="earnings">
                        <h3 class="mb-4">Earnings Overview</h3>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="stat-card">
                                    <h5>Total Earnings</h5>
                                    <h2 class="text-primary">$4,520.50</h2>
                                    <p class="text-muted">Last updated: Today</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="stat-card">
                                    <h5>This Month</h5>
                                    <h2 class="text-success">$1,250.00</h2>
                                    <p class="text-muted">March 2024</p>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Course</th>
                                        <th>Sales</th>
                                        <th>Earnings</th>
                                        <th>Last Sale</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Web Development Bootcamp</td>
                                        <td>25</td>
                                        <td>$2,500.00</td>
                                        <td>2024-03-15</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Dashboard End -->

<!-- Add SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.body.addEventListener('htmx:afterRequest', function(evt) {
    const response = JSON.parse(evt.detail.xhr.response);
    
    if (response.success) {
        Swal.fire({
            title: 'Success!',
            text: response.message,
            icon: 'success',
            confirmButtonColor: '#3085d6'
        }).then(() => {
            // Reset form
            evt.detail.target.reset();
            // Switch to courses tab
            document.querySelector('[href="#my-courses"]').click();
        });
    } else {
        Swal.fire({
            title: 'Error!',
            text: response.message,
            icon: 'error',
            confirmButtonColor: '#d33'
        });
    }
});

function deleteCourse(courseId) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('helper/delete-course.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ courseId: courseId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire(
                        'Deleted!',
                        'Your course has been deleted.',
                        'success'
                    ).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire(
                        'Error!',
                        data.message,
                        'error'
                    );
                }
            });
        }
    });
}

function editCourse(courseId) {
    // Implement edit functionality
    window.location.href = `edit-course.php?id=${courseId}`;
}
</script>

<!-- Remove multiple footer includes -->
<?php include 'footer.php'; ?> 