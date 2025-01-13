<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Session;
use App\Models\Statistics;

Session::start();

// Check if user is admin
if (Session::get('role') !== 'admin') {
    header('Location: login.php');
    exit();
}

$stats = new Statistics();
$totalStudents = $stats->getTotalStudents();
$totalTeachers = $stats->getTotalTeachers();
$totalCourses = $stats->getTotalCourses();
$topCourses = $stats->getTopCourses();
$recentActivities = $stats->getRecentActivities();
$categoryStats = $stats->getCategoryStats();

include 'header.php';
?>

<!-- Dashboard Start -->
<div class="container-fluid py-5">
    <div class="container">
        <div class="row">
            <!-- Sidebar/Tabs -->
            <div class="col-lg-3">
                <div class="nav flex-column nav-pills" role="tablist">
                    <a class="nav-link active" data-toggle="pill" href="#statistics">
                        <i class="fas fa-chart-bar mr-2"></i>Statistics
                    </a>
                    <a class="nav-link" data-toggle="pill" href="#teachers">
                        <i class="fas fa-chalkboard-teacher mr-2"></i>Teacher Verification
                    </a>
                    <a class="nav-link" data-toggle="pill" href="#courses">
                        <i class="fas fa-book mr-2"></i>Course Approval
                    </a>
                    <a class="nav-link" data-toggle="pill" href="#categories">
                        <i class="fas fa-folder mr-2"></i>Categories
                    </a>
                    <a class="nav-link" data-toggle="pill" href="#tags">
                        <i class="fas fa-tags mr-2"></i>Tags
                    </a>
                </div>
            </div>

            <!-- Content -->
            <div class="col-lg-9">
                <div class="tab-content">
                    <!-- Statistics Tab -->
                    <div class="tab-pane fade show active" id="statistics">
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
                                        <td><?= htmlspecialchars($course['title']) ?></td>
                                        <td><?= htmlspecialchars($course['teacher']) ?></td>
                                        <td><?= htmlspecialchars($course['total_students']) ?></td>
                                        <td><?= htmlspecialchars($course['category']) ?></td>
                                        <td>$<?= number_format($course['price'], 2) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Teachers Tab -->
                    <div class="tab-pane fade" id="teachers">
                        <h3 class="mb-4">Teacher Verification</h3>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Specialization</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Jane Smith</td>
                                        <td>jane@example.com</td>
                                        <td>Mathematics</td>
                                        <td><span class="status-badge status-pending">Pending</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-success">Approve</button>
                                            <button class="btn btn-sm btn-danger">Reject</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Courses Tab -->
                    <div class="tab-pane fade" id="courses">
                        <h3 class="mb-4">Course Approval</h3>
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
                                <tbody>
                                    <tr>
                                        <td>Advanced Python Programming</td>
                                        <td>Mike Johnson</td>
                                        <td>Programming</td>
                                        <td><span class="status-badge status-pending">Pending</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-success">Approve</button>
                                            <button class="btn btn-sm btn-danger">Reject</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Categories Tab -->
                    <div class="tab-pane fade" id="categories">
                        <h3 class="mb-4">Category Management</h3>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <input type="text" class="form-control" placeholder="New Category Name">
                                    <div class="input-group-append">
                                        <button class="btn btn-primary">Add Category</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Category Name</th>
                                        <th>Courses</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Web Development</td>
                                        <td>45</td>
                                        <td><span class="status-badge status-approved">Active</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-warning">Edit</button>
                                            <button class="btn btn-sm btn-danger">Delete</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tags Tab -->
                    <div class="tab-pane fade" id="tags">
                        <h3 class="mb-4">Tag Management</h3>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <input type="text" class="form-control" placeholder="Add multiple tags (comma separated)">
                                    <div class="input-group-append">
                                        <button class="btn btn-primary">Add Tags</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Tag Name</th>
                                        <th>Used In</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>JavaScript</td>
                                        <td>23 courses</td>
                                        <td><span class="status-badge status-approved">Active</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-warning">Edit</button>
                                            <button class="btn btn-sm btn-danger">Delete</button>
                                        </td>
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

<?php include 'footer.php'; ?> 