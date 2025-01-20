<?php 
require_once __DIR__ . '/../vendor/autoload.php';
use App\Models\Session;

Session::start(); // Make sure session is started before any session operations
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Edukate - Register</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="Free HTML Templates" name="keywords">
    <meta content="Free HTML Templates" name="description">

    <!-- Favicon -->
    <link href="../uploads/img/django.png" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Jost:wght@500;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet"> 
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">


    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/style.css" rel="stylesheet">

    <!-- Add these before the closing </head> tag -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="three.r134.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/vanta@latest/dist/vanta.net.min.js"></script>
    <!-- Import HTMX after Bootstrap -->
    <script src="https://unpkg.com/htmx.org@2.0.4" integrity="sha384-HGfztofotfshcF7+8n44JQL2oJmowVChPTg48S+jvZoztPfvwD79OC/LTtG6dMp+" crossorigin="anonymous"></script>

    <script>
    $(document).ready(function() {
        // Initialize Bootstrap components
        $('[data-toggle="dropdown"]').dropdown();
        
        // Optional: Close dropdowns when clicking outside
        $(document).click(function(e) {
            if (!$(e.target).closest('.dropdown').length) {
                $('.dropdown-menu').removeClass('show');
            }
        });
    });

    </script>
</head>

<body>
    <!-- Topbar Start -->
    <div class="container-fluid bg-dark">
        <div class="row py-2 px-lg-5">
            <div class="col-lg-6 text-center text-lg-left mb-2 mb-lg-0">
                <div class="d-inline-flex align-items-center text-white">
                    <small><i class="fa fa-phone-alt mr-2"></i>+012 345 6789</small>
                    <small class="px-3">|</small>
                    <small><i class="fa fa-envelope mr-2"></i>info@example.com</small>
                </div>
            </div>
            <div class="col-lg-6 text-center text-lg-right">
                <div class="d-inline-flex align-items-center">
                    <a class="text-white px-2" href="">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a class="text-white px-2" href="">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a class="text-white px-2" href="">
                        <i class="fab fa-linkedin-in"></i>
                    </a>
                    <a class="text-white px-2" href="">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a class="text-white pl-2" href="">
                        <i class="fab fa-youtube"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <!-- Topbar End -->

    <!-- Navbar Start -->
    <div class="container-fluid p-0">
        <nav class="navbar navbar-expand-lg bg-white navbar-light py-3 py-lg-0 px-lg-5">
            <a href="index.php" class="navbar-brand ml-lg-3">
                <h1 class="m-0 text-uppercase text-primary"><i class="fa fa-book-reader mr-3"></i>Edukate</h1>
            </a>
            <button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#navbarCollapse">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-between px-lg-3" id="navbarCollapse">
                <div class="navbar-nav mx-auto py-0">
                    <a href="index.php" class="nav-item nav-link">Home</a>
                    <a href="about.php" class="nav-item nav-link">About</a>
                    <?php if (Session::isLoggedIn()): ?>
                        <?php if (Session::get('role') === 'student'): ?>
                            <a href="courses.php" class="nav-item nav-link">Courses</a>
                            <a href="my-courses.php" class="nav-item nav-link">My Courses</a>
                        <?php elseif (Session::get('role') === 'teacher'): ?>
                            <a href="teacher.php" class="nav-item nav-link">
                                <i class="fa fa-chalkboard-teacher mr-2"></i>Teacher Dashboard
                            </a>
                        <?php endif; ?>
                    <?php endif; ?>
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">Pages</a>
                        <div class="dropdown-menu m-0">
                            <a href="detail.php" class="dropdown-item">Course Detail</a>
                            <a href="feature.php" class="dropdown-item">Our Features</a>
                            <a href="team.php" class="dropdown-item">Instructors</a>
                            <a href="testimonial.php" class="dropdown-item">Testimonial</a>
                        </div>
                    </div>
                    <a href="contact.php" class="nav-item nav-link">Contact</a>
                </div>

                <div class="navbar-nav">
                    <?php if(!Session::isLoggedIn()): ?>
                        <a href="login.php" class="btn btn-primary py-2 px-4 d-none d-lg-block">Login</a>
                    <?php else: ?>
                        <div class="nav-item dropdown">
                            <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
                                <i class="fa fa-user-circle mr-2"></i>
                                <?php 
                                $firstname = Session::get('firstname');
                                $lastname = Session::get('lastname');
                                echo htmlspecialchars($firstname . ' ' . $lastname); 
                                ?>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right m-0">
                                <?php if(Session::get('role') === 'teacher'): ?>
                                    <a href="teacher.php" class="dropdown-item">
                                        <i class="fa fa-chalkboard-teacher mr-2"></i>Teacher Dashboard
                                    </a>
                                <?php elseif(Session::get('role') === 'student'): ?>
                                    <a href="dashboard/student.php" class="dropdown-item">
                                        <i class="fa fa-graduation-cap mr-2"></i>My Learning
                                    </a>
                                <?php elseif(Session::get('role') === 'admin'): ?>
                                    <a href="admin-dashboard.php" class="dropdown-item">
                                        <i class="fa fa-cog mr-2"></i>Admin Dashboard
                                    </a>
                                <?php endif; ?>
                                
                                <a href="profile.php" class="dropdown-item">
                                    <i class="fa fa-user mr-2"></i>Profile
                                </a>
                                <div class="dropdown-divider"></div>
                                <a href="#" class="dropdown-item text-danger" 
                                   hx-post="helper/logout.php"
                                   hx-trigger="click"
                                   hx-confirm="Are you sure you want to logout?"
                                   hx-swap="none">
                                    <i class="fa fa-sign-out-alt mr-2"></i>Logout
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </div>
    <!-- Navbar End -->

    <script>
    document.body.addEventListener('htmx:afterRequest', function(evt) {
        if (evt.detail.target.classList.contains('text-danger')) { // Logout link
            const response = JSON.parse(evt.detail.xhr.response);
            
            if (response.success) {
                Swal.fire({
                    title: 'Success!',
                    text: response.message,
                    icon: 'success',
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    window.location.href = response.redirect;
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