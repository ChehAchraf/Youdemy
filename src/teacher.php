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
                                        <th>Course</th>
                                        <th>Category</th>
                                        <th>Price</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="course-table-body">
                                    <?php
                                    if ($courses) {
                                        foreach($courses as $course) {
                                            $statusClass = match($course->status_label) {
                                                'Approved' => 'success',
                                                'Rejected' => 'danger',
                                                default => 'warning'
                                            };
                                                
                                            echo "<tr id='course-row-{$course->id}'>
                                                <td>
                                                    <img src='../{$course->thumbnail}' alt='thumbnail' class='img-thumbnail mr-2' style='width: 50px; height: 50px; object-fit: cover;'>
                                                    " . htmlspecialchars($course->title) . "
                                                </td>
                                                <td>" . htmlspecialchars($course->category_name) . "</td>
                                                <td>$" . number_format($course->price, 2) . "</td>
                                                <td><span class='badge badge-{$statusClass}'>{$course->status_label}</span></td>
                                                <td class='d-flex justify-content-center'>
                                                    <button class='btn  btn-sm btn-primary' onclick='editCourse({$course->id})'>
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
                              hx-swap="innerHTML"
                              id="courseForm">
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
                                <label for="description">Course Description</label>
                                <div id="description" style="height: 300px;"></div>
                                <input type="hidden" name="description" id="descriptionInput">
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
                            <div class="form-group">
                                <label>Course Media (Video or Downloadable Material)</label>
                                <div class="custom-file">
                                    <input type="file" 
                                           class="custom-file-input" 
                                           id="courseMedia" 
                                           name="media" 
                                           accept=".mp4,.webm,.ogg,.pdf,.doc,.docx,.ppt,.pptx,.zip,.rar">
                                    <label class="custom-file-label" for="courseMedia">Choose file</label>
                                </div>
                                <small class="form-text text-muted">
                                    Supported formats:<br>
                                    - Videos: MP4, WebM, OGG<br>
                                    - Documents: PDF, DOC, DOCX, PPT, PPTX<br>
                                    - Archives: ZIP, RAR
                                </small>
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

<!-- Add Quill CSS -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">

<!-- Add SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Add Quill JS -->
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>

<!-- Edit Course Modal -->
<div class="modal fade" id="editCourseModal" tabindex="-1" role="dialog" aria-labelledby="editCourseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCourseModalLabel">Edit Course</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="editCourseForm" 
                      hx-post="helper/edit-course.php" 
                      hx-encoding="multipart/form-data"
                      hx-target="#course-table-body"
                      hx-swap="innerHTML">
                    <input type="hidden" name="courseId" id="editCourseId">
                    <div class="form-group">
                        <label>Course Title</label>
                        <input type="text" name="title" id="editTitle" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Course Description</label>
                        <div id="editDescription" style="height: 300px;"></div>
                        <input type="hidden" name="description" id="editDescriptionInput">
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Category</label>
                            <select name="categoryId" id="editCategory" class="form-control" required>
                                <option value="">Select Category</option>
                                <?php
                                $categories = $db->query("SELECT id, name FROM categories ORDER BY name");
                                while ($category = $categories->fetch(PDO::FETCH_OBJ)) {
                                    echo "<option value='" . $category->id . "'>" . htmlspecialchars($category->name) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Price ($)</label>
                            <input type="number" name="price" id="editPrice" step="0.01" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>New Thumbnail (optional)</label>
                            <input type="file" name="thumbnail" class="form-control" accept="image/*">
                            <small class="form-text text-muted">Leave empty to keep current thumbnail</small>
                        </div>
                        <div class="form-group col-md-6">
                            <label>New Course Content (optional)</label>
                            <input type="file" name="content" class="form-control">
                            <small class="form-text text-muted">Leave empty to keep current content</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Quill Configuration
        const quillConfig = {
            theme: 'snow',
            modules: {
                toolbar: [
                    ['bold', 'italic', 'underline', 'strike'],
                    ['blockquote', 'code-block'],
                    [{ 'header': 1 }, { 'header': 2 }],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    [{ 'script': 'sub'}, { 'script': 'super' }],
                    [{ 'indent': '-1'}, { 'indent': '+1' }],
                    [{ 'direction': 'rtl' }],
                    [{ 'size': ['small', false, 'large', 'huge'] }],
                    [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                    [{ 'color': [] }, { 'background': [] }],
                    [{ 'font': [] }],
                    [{ 'align': [] }],
                    ['clean'],
                    ['link', 'image', 'video']
                ]
            }
        };

        // Initialize Quill for add course form
        const addEditor = new Quill('#description', quillConfig);
        let editEditor = null;

        // Handle edit course modal
        $('#editCourseModal').on('shown.bs.modal', function () {
            if (!editEditor) {
                editEditor = new Quill('#editDescription', quillConfig);
                const courseId = document.getElementById('editCourseId').value;
                if (courseId) {
                    fetch(`helper/get-course.php?id=${courseId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success && data.course.description) {
                                editEditor.root.innerHTML = data.course.description;
                            }
                        });
                }
            }
        });

        $('#editCourseModal').on('hidden.bs.modal', function () {
            if (editEditor) {
                editEditor = null;
                document.querySelector('#editDescription').innerHTML = '';
            }
        });

        // HTMX Events
        htmx.on('#editCourseForm', 'htmx:configRequest', function(evt) {
            if (editEditor) {
                document.getElementById('editDescriptionInput').value = editEditor.root.innerHTML;
            }
        });

        htmx.on('#courseForm', 'htmx:configRequest', function(evt) {
            evt.detail.parameters['description'] = addEditor.root.innerHTML;
        });

        htmx.on('#editCourseForm', 'htmx:beforeRequest', function(evt) {
            // Show loading state
            Swal.fire({
                title: 'Updating...',
                text: 'Please wait while we update the course',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });
        });

        htmx.on('#editCourseForm', 'htmx:afterRequest', function(evt) {
            let response;
            try {
                response = JSON.parse(evt.detail.xhr.response);
            } catch (e) {
                console.error('Response:', evt.detail.xhr.response);
                console.error('Parse error:', e);
                Swal.fire({
                    title: 'Error',
                    text: 'Server error occurred. Please check the console for details.',
                    icon: 'error'
                });
                return;
            }

            if (response.success) {
                $('#editCourseModal').modal('hide');
                document.getElementById('course-table-body').innerHTML = response.html;
                Swal.fire({
                    title: 'Success',
                    text: response.message,
                    icon: 'success'
                });
            } else {
                Swal.fire({
                    title: 'Error',
                    text: response.message || 'Failed to update course',
                    icon: 'error'
                });
            }
        });

        htmx.on('#editCourseForm', 'htmx:error', function(evt) {
            console.error('HTMX Error:', evt.detail.error);
            console.error('Response:', evt.detail.xhr.response);
            Swal.fire({
                title: 'Error',
                text: 'Failed to update course. Please try again.',
                icon: 'error'
            });
        });

        // Update file input label with selected filename
        document.querySelector('.custom-file-input').addEventListener('change', function(e) {
            var fileName = e.target.files[0].name;
            var nextSibling = e.target.nextElementSibling;
            nextSibling.innerText = fileName;

            // Check file size
            var fileSize = e.target.files[0].size;
            var maxSize = 500 * 1024 * 1024; // 500MB
            if (fileSize > maxSize) {
                Swal.fire({
                    icon: 'error',
                    title: 'File Too Large',
                    text: 'Please select a file smaller than 500MB'
                });
                e.target.value = ''; // Clear the file input
                nextSibling.innerText = 'Choose file';
                return;
            }

            // Check file type
            var fileExtension = fileName.split('.').pop().toLowerCase();
            var allowedExtensions = ['mp4', 'webm', 'ogg', 'pdf', 'doc', 'docx', 'ppt', 'pptx', 'zip', 'rar'];
            if (!allowedExtensions.includes(fileExtension)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid File Type',
                    text: 'Please select a supported file format'
                });
                e.target.value = '';
                nextSibling.innerText = 'Choose file';
            }
        });
    });

    function editCourse(courseId) {
        fetch(`helper/get-course.php?id=${courseId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const course = data.course;
                    document.getElementById('editCourseId').value = course.id;
                    document.getElementById('editTitle').value = course.title;
                    document.getElementById('editCategory').value = course.categoryId;
                    document.getElementById('editPrice').value = course.price;
                    
                    $('#editCourseModal').modal('show');
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'Failed to load course details', 'error');
            });
    }
</script>

<?php include 'footer.php'; ?> 