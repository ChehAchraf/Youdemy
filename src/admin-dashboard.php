<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Session;
use App\Models\Statistics;
use App\Models\Course;
use App\Models\Admin;
use App\Models\AdminCourse;

Session::start();

// Check if user is admin
if (Session::get('role') !== 'admin') {
    header('Location: login.php');
    exit();
}

$admin = new Admin();
$stats = $admin->getStatistics();
$topCourses = $admin->getTopCourses();
$recentActivities = $admin->getRecentActivities();
$categoryStats = $admin->getCategoryStats();

$totalStudents = $stats['students'];
$totalTeachers = $stats['teachers'];
$totalCourses = $stats['courses'];

$courseModel = new AdminCourse();

include 'header.php';
?>

<!-- Dashboard Start -->
<div class="container-fluid py-5">
    <div class="container">
        <div class="row">
            <!-- Sidebar/Tabs -->
            <div class="col-lg-3">
                <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist">
                    <button class="nav-link active" data-toggle="pill" data-target="#statistics" type="button" role="tab">
                        <i class="fas fa-chart-bar mr-2"></i>Statistics
                    </button>
                    <button class="nav-link" data-toggle="pill" data-target="#teachers" type="button" role="tab">
                        <i class="fas fa-chalkboard-teacher mr-2"></i>Teacher Verification
                    </button>
                    <button class="nav-link" data-toggle="pill" data-target="#courses" type="button" role="tab">
                        <i class="fas fa-book mr-2"></i>Course Approval
                    </button>
                    <button class="nav-link" data-toggle="pill" data-target="#categories" type="button" role="tab">
                        <i class="fas fa-folder mr-2"></i>Categories
                    </button>
                    <button class="nav-link" data-toggle="pill" data-target="#tags" type="button" role="tab">
                        <i class="fas fa-tags mr-2"></i>Tags
                    </button>
                    <button class="nav-link" data-toggle="pill" data-target="#pending-courses" type="button" role="tab">
                        <i class="fas fa-clock mr-2"></i>Pending Courses
                    </button>
                </div>
            </div>

            <!-- Content -->
            <div class="col-lg-9">
                <div class="tab-content" id="v-pills-tabContent">
                    <!-- Statistics Tab -->
                    <div class="tab-pane fade show active" id="statistics" role="tabpanel" aria-labelledby="statistics-tab">
                        <h3 class="mb-4">Dashboard Statistics</h3>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="stat-card text-center">
                                    <i class="fas fa-users fa-3x mb-3 text-primary"></i>
                                    <div class="stat-number"><?= htmlspecialchars($totalStudents) ?></div>
                                    <div class="stat-label">Total Students</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="stat-card text-center">
                                    <i class="fas fa-chalkboard-teacher fa-3x mb-3 text-success"></i>
                                    <div class="stat-number"><?= htmlspecialchars($totalTeachers) ?></div>
                                    <div class="stat-label">Total Teachers</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="stat-card text-center">
                                    <i class="fas fa-book fa-3x mb-3 text-info"></i>
                                    <div class="stat-number"><?= htmlspecialchars($totalCourses) ?></div>
                                    <div class="stat-label">Total Courses</div>
                                </div>
                            </div>
                        </div>

                        <!-- Top Courses -->
                        <div class="table-responsive mt-4">
                            <h4 class="mb-3">Top Performing Courses</h4>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Course Name</th>
                                        <th>Teacher</th>
                                        <th>Students</th>
                                        <th>Category</th>
                                        <th>Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($topCourses as $course): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($course->title) ?></td>
                                        <td><?= htmlspecialchars($course->teacher_name) ?></td>
                                        <td><?= htmlspecialchars($course->enrollment_count) ?></td>
                                        <td><?= htmlspecialchars($course->category_name) ?></td>
                                        <td>$<?= number_format($course->price, 2) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Teachers Tab -->
                    <div class="tab-pane fade" id="teachers" role="tabpanel" aria-labelledby="teachers-tab">
                        <h3 class="mb-4">Teacher Verification</h3>
                        
                        <!-- Nav tabs for teacher status -->
                        <ul class="nav nav-tabs mb-3">
                            <li class="nav-item">
                                <a class="nav-link active" data-toggle="tab" href="#pending-teachers">Pending</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#all-teachers">All Teachers</a>
                            </li>
                        </ul>

                        <div class="tab-content">
                            <!-- Pending Teachers -->
                            <div class="tab-pane fade show active" id="pending-teachers">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Specialization</th>
                                                <th>Registration Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $pendingTeachers = $admin->getPendingTeachers();
                                            foreach ($pendingTeachers as $teacher) {
                                                echo "<tr id='teacher-{$teacher->id}'>
                                                    <td>" . htmlspecialchars($teacher->firstname . ' ' . $teacher->lastname) . "</td>
                                                    <td>" . htmlspecialchars($teacher->email) . "</td>
                                                    <td>" . htmlspecialchars($teacher->specialization) . "</td>
                                                    <td>" . date('Y-m-d', strtotime($teacher->created_at)) . "</td>
                                                    <td>
                                                        <button class='btn btn-sm btn-success' onclick='verifyTeacher({$teacher->id}, \"approve\")'>
                                                            <i class='fas fa-check'></i> Approve
                                                        </button>
                                                        <button class='btn btn-sm btn-danger' onclick='verifyTeacher({$teacher->id}, \"reject\")'>
                                                            <i class='fas fa-times'></i> Reject
                                                        </button>
                                                    </td>
                                                </tr>";
                                            }
                                            if (empty($pendingTeachers)) {
                                                echo "<tr><td colspan='5' class='text-center'>No pending teacher verifications</td></tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- All Teachers -->
                            <div class="tab-pane fade" id="all-teachers">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Specialization</th>
                                                <th>Status</th>
                                                <th>Registration Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $allTeachers = $admin->getAllTeachers();
                                            foreach ($allTeachers as $teacher) {
                                                $statusClass = match($teacher->verification_status) {
                                                    'approved' => 'success',
                                                    'rejected' => 'danger',
                                                    'pending' => 'warning',
                                                    default => 'secondary'
                                                };
                                                
                                                echo "<tr>
                                                    <td>" . htmlspecialchars($teacher->firstname . ' ' . $teacher->lastname) . "</td>
                                                    <td>" . htmlspecialchars($teacher->email) . "</td>
                                                    <td>" . htmlspecialchars($teacher->specialization) . "</td>
                                                    <td><span class='badge badge-{$statusClass}'>" . ucfirst($teacher->verification_status) . "</span></td>
                                                    <td>" . date('Y-m-d', strtotime($teacher->created_at)) . "</td>
                                                </tr>";
                                            }
                                            if (empty($allTeachers)) {
                                                echo "<tr><td colspan='5' class='text-center'>No teachers found</td></tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Courses Tab -->
                    <div class="tab-pane fade" id="courses" role="tabpanel" aria-labelledby="courses-tab">
                        <h3 class="mb-4">Course Management</h3>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Course Title</th>
                                        <th>Teacher</th>
                                        <th>Category</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="courses-list">
                                    <?php
                                    $courses = $courseModel->getAllCourses();

                                    foreach($courses as $course) {
                                        $statusClass = match($course->status_label) {
                                            'Approved' => 'success',
                                            'Rejected' => 'danger',
                                            default => 'warning'
                                        };
                                        
                                        echo "<tr id='course-{$course->id}'>
                                            <td>
                                                <img src='../{$course->thumbnail}' alt='thumbnail' class='img-thumbnail mr-2' style='width: 50px; height: 50px; object-fit: cover;'>
                                                " . htmlspecialchars($course->title) . "
                                            </td>
                                            <td>" . htmlspecialchars($course->teacher_firstname . ' ' . $course->teacher_lastname) . "</td>
                                            <td>" . htmlspecialchars($course->category_name) . "</td>
                                            <td><span class='badge badge-{$statusClass}'>{$course->status_label}</span></td>
                                            <td>
                                                <button class='btn btn-sm btn-success' 
                                                        hx-post='helper/approve-course.php'
                                                        hx-vals='{\"courseId\": " . $course->id . ", \"action\": \"approve\"}'
                                                        hx-confirm='Are you sure you want to approve this course?'
                                                        hx-target='#course-{$course->id}'
                                                        hx-swap='outerHTML'
                                                        " . ($course->isApproved || $course->rejectedBy ? 'disabled' : '') . ">
                                                    <i class='fas fa-check'></i>
                                                </button>
                                                <button class='btn btn-sm btn-danger' 
                                                        onclick='rejectCourseWithReason({$course->id})'
                                                        " . ($course->isApproved || $course->rejectedBy ? 'disabled' : '') . ">
                                                    <i class='fas fa-times'></i>
                                                </button>
                                                <button class='btn btn-sm btn-secondary'
                                                        hx-post='helper/delete-course.php'
                                                        hx-vals='{\"courseId\": " . $course->id . "}'
                                                        hx-confirm='Are you sure you want to delete this course? This cannot be undone.'
                                                        hx-target='#course-{$course->id}'
                                                        hx-swap='outerHTML'>
                                                    <i class='fas fa-trash'></i>
                                                </button>
                                            </td>
                                        </tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Categories Tab -->
                    <div class="tab-pane fade" id="categories" role="tabpanel" aria-labelledby="categories-tab">
                        <h3 class="mb-4">Category Management</h3>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <form id="addCategoryForm" class="input-group">
                                    <input type="text" name="name" class="form-control" placeholder="Category Name" required>
                                    <input type="text" name="description" class="form-control" placeholder="Description (optional)">
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-primary">Add Category</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Category Name</th>
                                        <th>Description</th>
                                        <th>Courses</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="categoriesList">
                                    <?php
                                    $categories = $admin->getAllCategories();
                                    foreach($categories as $category) {
                                        echo "<tr id='category-{$category->id}'>
                                            <td>" . htmlspecialchars($category->name) . "</td>
                                            <td>" . htmlspecialchars($category->description ?? '') . "</td>
                                            <td>" . htmlspecialchars($category->course_count) . "</td>
                                            <td>
                                                <button class='btn btn-sm btn-warning' onclick='editCategory({$category->id}, \"{$category->name}\", \"{$category->description}\")'>
                                                    <i class='fas fa-edit'></i>
                                                </button>
                                                <button class='btn btn-sm btn-danger' onclick='deleteCategory({$category->id})'>
                                                    <i class='fas fa-trash'></i>
                                                </button>
                                            </td>
                                        </tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tags Tab -->
                    <div class="tab-pane fade" id="tags" role="tabpanel" aria-labelledby="tags-tab">
                        <h3 class="mb-4">Tag Management</h3>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <form id="addTagsForm" class="input-group">
                                    <input type="text" name="tags" class="form-control" placeholder="Add multiple tags (comma separated)" required>
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-primary">Add Tags</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Tag Name</th>
                                        <th>Used In</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="tagsList">
                                    <?php
                                    $tags = $admin->getAllTags();
                                    foreach($tags as $tag) {
                                        echo "<tr id='tag-{$tag->id}'>
                                            <td>" . htmlspecialchars($tag->name) . "</td>
                                            <td>" . htmlspecialchars($tag->course_count) . " courses</td>
                                            <td>
                                                <button class='btn btn-sm btn-warning' onclick='editTag({$tag->id}, \"{$tag->name}\")'>
                                                    <i class='fas fa-edit'></i>
                                                </button>
                                                <button class='btn btn-sm btn-danger' onclick='deleteTag({$tag->id})'>
                                                    <i class='fas fa-trash'></i>
                                                </button>
                                            </td>
                                        </tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Pending Courses Tab -->
                    <div class="tab-pane fade" id="pending-courses" role="tabpanel" aria-labelledby="pending-courses-tab">
                        <h3 class="mb-4">Pending & Deleted Courses</h3>
                        
                        <!-- Nav tabs for pending and deleted courses -->
                        <ul class="nav nav-tabs mb-3">
                            <li class="nav-item">
                                <a class="nav-link active" data-toggle="tab" href="#pending">Pending Courses</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#deleted">Deleted Courses</a>
                            </li>
                        </ul>

                        <div class="tab-content">
                            <!-- Pending Courses -->
                            <div class="tab-pane fade show active" id="pending">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Course</th>
                                        <th>Teacher</th>
                                        <th>Category</th>
                                        <th>Price</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $pendingCourses = $courseModel->getPendingCourses();

                                    if ($pendingCourses) {
                                        foreach($pendingCourses as $course) {
                                            echo "<tr>
                                                <td>
                                                    <img src='{$course->thumbnail}' alt='thumbnail' class='img-thumbnail mr-2' style='width: 50px; height: 50px; object-fit: cover;'>
                                                    " . htmlspecialchars($course->title) . "
                                                </td>
                                                <td>" . htmlspecialchars($course->teacher_firstname . ' ' . $course->teacher_lastname) . "</td>
                                                <td>" . htmlspecialchars($course->category_name) . "</td>
                                                <td>$" . number_format($course->price, 2) . "</td>
                                                <td>
                                                    <button class='btn btn-sm btn-success' onclick='approveCourse({$course->id})'
                                                            " . ($course->isApproved || $course->rejectedBy ? 'disabled' : '') . ">
                                                        <i class='fas fa-check'></i> Approve
                                                    </button>
                                                    <button class='btn btn-sm btn-danger' onclick='rejectCourse({$course->id})'
                                                            " . ($course->isApproved || $course->rejectedBy ? 'disabled' : '') . ">
                                                        <i class='fas fa-times'></i> Reject
                                                    </button>
                                                </td>
                                            </tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='5' class='text-center'>No pending courses</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                                </div>
                            </div>

                            <!-- Deleted Courses -->
                            <div class="tab-pane fade" id="deleted">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Course</th>
                                                <th>Teacher</th>
                                                <th>Category</th>
                                                <th>Price</th>
                                                <th>Deleted At</th>
                                                <th>Deleted By</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $deletedCourses = $courseModel->getDeletedCourses();

                                            if ($deletedCourses) {
                                                foreach($deletedCourses as $course) {
                                                    echo "<tr>
                                                        <td>
                                                            <img src='{$course->thumbnail}' alt='thumbnail' class='img-thumbnail mr-2' style='width: 50px; height: 50px; object-fit: cover;'>
                                                            " . htmlspecialchars($course->title) . "
                                                        </td>
                                                        <td>" . htmlspecialchars($course->teacher_firstname . ' ' . $course->teacher_lastname) . "</td>
                                                        <td>" . htmlspecialchars($course->category_name) . "</td>
                                                        <td>$" . number_format($course->price, 2) . "</td>
                                                        <td>" . date('Y-m-d H:i:s', strtotime($course->deleted_at)) . "</td>
                                                        <td>" . htmlspecialchars($course->deleted_by_firstname . ' ' . $course->deleted_by_lastname) . "</td>
                                                        <td>
                                                            <button class='btn btn-sm btn-success' onclick='restoreCourse({$course->id})'>
                                                                <i class='fas fa-undo'></i> Restore
                                                            </button>
                                                        </td>
                                                    </tr>";
                                                }
                                            } else {
                                                echo "<tr><td colspan='7' class='text-center'>No deleted courses</td></tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Dashboard End -->

<!-- Add the custom dashboard styles -->
<style>
.dashboard-tab {
    display: none;
}
.dashboard-tab.active {
    display: block;
}
.stat-card {
    background: #fff;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 0 15px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}
.stat-number {
    font-size: 2.5rem;
    font-weight: bold;
    color: #17a2b8;
}
.table-responsive {
    background: #fff;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 0 15px rgba(0,0,0,0.1);
}
.nav-pills .nav-link.active {
    background-color: #17a2b8;
}
.status-badge {
    padding: 5px 10px;
    border-radius: 15px;
    font-size: 12px;
}
.status-pending {
    background-color: #ffeeba;
    color: #856404;
}
.status-approved {
    background-color: #d4edda;
    color: #155724;
}
.status-rejected {
    background-color: #f8d7da;
    color: #721c24;
}
</style>

<!-- Add these script tags before your existing scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>

<!-- Add this script for tab initialization -->
<script>
$(document).ready(function() {
    // Initialize Bootstrap tabs
    $('.nav-pills a').on('click', function (e) {
        e.preventDefault();
        $(this).tab('show');
    });

    // Show first tab on load
    $('#statistics-tab').tab('show');
});
</script>

<!-- Add this JavaScript for handling approvals -->
<script>
function approveCourse(courseId) {
    Swal.fire({
        title: 'Approve Course',
        text: 'Are you sure you want to approve this course?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, approve it!'
    }).then((result) => {
        if (result.isConfirmed) {
            sendApprovalRequest(courseId, 'approve');
        }
    });
}

function rejectCourse(courseId) {
    Swal.fire({
        title: 'Reject Course',
        text: 'Please provide a reason for rejection:',
        input: 'textarea',
        inputPlaceholder: 'Enter rejection reason...',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Reject',
        inputValidator: (value) => {
            if (!value) {
                return 'You need to provide a reason!';
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            sendApprovalRequest(courseId, 'reject', result.value);
        }
    });
}

function sendApprovalRequest(courseId, action, reason = null) {
    fetch('helper/approve-course.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ 
            courseId: courseId, 
            action: action,
            reason: reason 
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                title: 'Success!',
                text: data.message,
                icon: 'success'
            }).then(() => {
                location.reload();
            });
        } else {
            throw new Error(data.message);
        }
    })
    .catch(error => {
        Swal.fire({
            title: 'Error!',
            text: error.message,
            icon: 'error'
        });
    });
}

function rejectCourseWithReason(courseId) {
    Swal.fire({
        title: 'Reject Course',
        text: 'Please provide a reason for rejection:',
        input: 'textarea',
        inputPlaceholder: 'Enter rejection reason...',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Reject',
        inputValidator: (value) => {
            if (!value) {
                return 'You need to provide a reason!';
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            htmx.ajax('POST', 'helper/approve-course.php', {
                target: `#course-${courseId}`,
                swap: 'outerHTML',
                values: {
                    courseId: courseId,
                    action: 'reject',
                    reason: result.value
                }
            });
        }
    });
}

// Add HTMX event handlers
document.body.addEventListener('htmx:afterRequest', function(evt) {
    if (evt.detail.successful) {
        const response = JSON.parse(evt.detail.xhr.response);
        Swal.fire({
            title: 'Success!',
            text: response.message,
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
        });
    } else {
        const response = JSON.parse(evt.detail.xhr.response);
        Swal.fire({
            title: 'Error!',
            text: response.message,
            icon: 'error'
        });
    }
});
</script>

<!-- Add this JavaScript for category management -->
<script>
document.getElementById('addCategoryForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.append('action', 'add');
    
    fetch('helper/manage-category.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                title: 'Success!',
                text: data.message,
                icon: 'success'
            }).then(() => {
                location.reload();
            });
        } else {
            throw new Error(data.message);
        }
    })
    .catch(error => {
        Swal.fire({
            title: 'Error!',
            text: error.message,
            icon: 'error'
        });
    });
});

function editCategory(id, name, description) {
    Swal.fire({
        title: 'Edit Category',
        html:
            `<input id="categoryName" class="swal2-input" value="${name}" placeholder="Category Name">
            <input id="categoryDescription" class="swal2-input" value="${description}" placeholder="Description">`,
        focusConfirm: false,
        preConfirm: () => {
            return {
                name: document.getElementById('categoryName').value,
                description: document.getElementById('categoryDescription').value
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('action', 'update');
            formData.append('id', id);
            formData.append('name', result.value.name);
            formData.append('description', result.value.description);

            fetch('helper/manage-category.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Success!', data.message, 'success')
                    .then(() => location.reload());
                } else {
                    throw new Error(data.message);
                }
            })
            .catch(error => {
                Swal.fire('Error!', error.message, 'error');
            });
        }
    });
}

function deleteCategory(id) {
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
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('id', id);

            fetch('helper/manage-category.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Deleted!', data.message, 'success')
                    .then(() => location.reload());
                } else {
                    throw new Error(data.message);
                }
            })
            .catch(error => {
                Swal.fire('Error!', error.message, 'error');
            });
        }
    });
}
</script>

<!-- Add this JavaScript for tag management -->
<script>
document.getElementById('addTagsForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.append('action', 'add');
    
    fetch('helper/manage-tags.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                title: 'Success!',
                text: data.message,
                icon: 'success'
            }).then(() => {
                location.reload();
            });
        } else {
            throw new Error(data.message);
        }
    })
    .catch(error => {
        Swal.fire({
            title: 'Error!',
            text: error.message,
            icon: 'error'
        });
    });
});

function editTag(id, name) {
    Swal.fire({
        title: 'Edit Tag',
        input: 'text',
        inputValue: name,
        showCancelButton: true,
        inputValidator: (value) => {
            if (!value) {
                return 'Tag name cannot be empty!';
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('action', 'update');
            formData.append('id', id);
            formData.append('name', result.value);

            fetch('helper/manage-tags.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Success!', data.message, 'success')
                    .then(() => location.reload());
                } else {
                    throw new Error(data.message);
                }
            })
            .catch(error => {
                Swal.fire('Error!', error.message, 'error');
            });
        }
    });
}

function deleteTag(id) {
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
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('id', id);

            fetch('helper/manage-tags.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Deleted!', data.message, 'success')
                    .then(() => location.reload());
                } else {
                    throw new Error(data.message);
                }
            })
            .catch(error => {
                Swal.fire('Error!', error.message, 'error');
            });
        }
    });
}
</script>

<!-- Add this JavaScript for course restoration -->
<script>
function restoreCourse(courseId) {
    Swal.fire({
        title: 'Restore Course',
        text: 'Are you sure you want to restore this course?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, restore it!'
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('courseId', courseId);

            fetch('helper/restore-course.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Restored!', data.message, 'success')
                    .then(() => location.reload());
                } else {
                    throw new Error(data.message);
                }
            })
            .catch(error => {
                Swal.fire('Error!', error.message, 'error');
            });
        }
    });
}
</script>

<script>
function verifyTeacher(teacherId, action) {
    if (action === 'reject') {
        Swal.fire({
            title: 'Reject Teacher',
            text: 'Please provide a reason for rejection:',
            input: 'textarea',
            inputPlaceholder: 'Enter rejection reason...',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Reject',
            inputValidator: (value) => {
                if (!value) {
                    return 'You need to provide a reason!';
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                sendVerificationRequest(teacherId, action, result.value);
            }
        });
    } else {
        Swal.fire({
            title: 'Approve Teacher',
            text: 'Are you sure you want to approve this teacher?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, approve'
        }).then((result) => {
            if (result.isConfirmed) {
                sendVerificationRequest(teacherId, action);
            }
        });
    }
}

function sendVerificationRequest(teacherId, action, reason = null) {
    // Show loading state
    Swal.fire({
        title: 'Processing...',
        text: 'Please wait while we process your request.',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        willOpen: () => {
            Swal.showLoading();
        }
    });

    const requestData = {
        teacherId: teacherId,
        action: action,
        reason: reason
    };

    console.log('Sending request with data:', requestData);

    fetch('helper/verify-teacher.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(requestData)
    })
    .then(response => {
        console.log('Raw response:', response);
        return response.text();
    })
    .then(text => {
        console.log('Response text:', text);
        try {
            return JSON.parse(text);
        } catch (e) {
            console.error('JSON parse error:', e);
            throw new Error('Invalid response format');
        }
    })
    .then(data => {
        console.log('Parsed response:', data);
        if (data.success) {
            Swal.fire({
                title: 'Success!',
                text: data.message,
                icon: 'success'
            }).then(() => {
                // Remove the row from pending teachers
                if (document.getElementById(`teacher-${teacherId}`)) {
                    document.getElementById(`teacher-${teacherId}`).remove();
                }
                // Refresh the page to update all teachers list
                location.reload();
            });
        } else {
            throw new Error(data.message || 'Failed to process request');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            title: 'Error!',
            text: error.message || 'An error occurred while processing your request',
            icon: 'error'
        });
    });
}
</script>

<?php include 'footer.php'; ?> 