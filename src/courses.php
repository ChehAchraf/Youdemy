<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\PublicCourse;
use App\Models\Database;
use App\Models\Session;
use App\Models\CourseSearch;

Session::start();

// Initialize search
$search = new CourseSearch();
$db = Database::getInstance()->getConnection();

// Get all categories for the filter
$categories = $db->query("SELECT id, name FROM categories ORDER BY name")->fetchAll(PDO::FETCH_OBJ);
$tags = $search->getAllTags();

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
    .search-section {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 30px;
    }
    .selected-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 10px;
    }
    .tag-badge {
        background: #e9ecef;
        padding: 5px 10px;
        border-radius: 15px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    .remove-tag {
        cursor: pointer;
        color: #dc3545;
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

<!-- Search Section Start -->
<div class="container-fluid">
    <div class="container">
        <div class="search-section">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="searchQuery">Search Courses</label>
                        <input type="text" id="searchQuery" class="form-control" placeholder="Search by title...">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="categoryFilter">Category</label>
                        <select id="categoryFilter" class="form-control">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category->id; ?>"><?php echo htmlspecialchars($category->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="tagInput">Tags</label>
                        <div class="input-group">
                            <input type="text" id="tagInput" class="form-control" placeholder="Enter a tag...">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="button" onclick="addTag()">Add</button>
                            </div>
                        </div>
                        <small class="form-text text-muted">Press Enter or click Add to add a tag</small>
                    </div>
                </div>
            </div>
            <div class="selected-tags" id="selectedTags"></div>
        </div>
    </div>
</div>
<!-- Search Section End -->

<!-- Courses Start -->
<div class="container-fluid py-5">
    <div class="container py-5">
        <div class="row" id="courseResults">
            <!-- Course results will be loaded here -->
        </div>
        <div class="row mt-4">
            <div class="col-12">
                <nav aria-label="Course pagination">
                    <ul class="pagination justify-content-center" id="pagination">
                        <!-- Pagination will be loaded here -->
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>
<!-- Courses End -->

<script>
let selectedTags = [];
let currentPage = 1;
let searchTimeout;

function addTag() {
    const tagInput = document.getElementById('tagInput');
    const tag = tagInput.value.trim().toLowerCase();
    
    if (tag && !selectedTags.includes(tag)) {
        selectedTags.push(tag);
        updateSelectedTags();
        searchCourses();
    }
    
    tagInput.value = '';
}

function updateSelectedTags() {
    const container = document.getElementById('selectedTags');
    container.innerHTML = selectedTags.map(tag => `
        <span class="tag-badge">
            ${tag}
            <span class="remove-tag" onclick="removeTag('${tag}')">&times;</span>
        </span>
    `).join('');
}

function removeTag(tag) {
    selectedTags = selectedTags.filter(t => t !== tag);
    updateSelectedTags();
    searchCourses();
}

function updatePagination(currentPage, totalPages) {
    const pagination = document.getElementById('pagination');
    let html = '';

    if (currentPage > 1) {
        html += `
            <li class="page-item">
                <a class="page-link" href="#" onclick="goToPage(${currentPage - 1})">&laquo;</a>
            </li>
        `;
    }

    const startPage = Math.max(1, currentPage - 2);
    const endPage = Math.min(totalPages, currentPage + 2);

    for (let i = startPage; i <= endPage; i++) {
        html += `
            <li class="page-item ${i === currentPage ? 'active' : ''}">
                <a class="page-link" href="#" onclick="goToPage(${i})">${i}</a>
            </li>
        `;
    }

    if (currentPage < totalPages) {
        html += `
            <li class="page-item">
                <a class="page-link" href="#" onclick="goToPage(${currentPage + 1})">&raquo;</a>
            </li>
        `;
    }

    pagination.innerHTML = html;
}

function goToPage(page) {
    currentPage = page;
    searchCourses();
}

function searchCourses() {
    const query = document.getElementById('searchQuery').value;
    const category = document.getElementById('categoryFilter').value;
    const tags = selectedTags.join(',');

    const url = `helper/search-courses.php?query=${encodeURIComponent(query)}&category=${category}&tags=${tags}&page=${currentPage}`;

    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('courseResults').innerHTML = data.html;
                updatePagination(data.currentPage, data.totalPages);
            } else {
                console.error('Search failed:', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

// Event Listeners
document.getElementById('searchQuery').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(searchCourses, 500);
});

document.getElementById('categoryFilter').addEventListener('change', searchCourses);

// Add tag on Enter key press
document.getElementById('tagInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        addTag();
    }
});

// Initial search
document.addEventListener('DOMContentLoaded', searchCourses);
</script>

<?php include 'footer.php'; ?> 