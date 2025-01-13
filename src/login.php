<?php 
require_once __DIR__ . '/../vendor/autoload.php';
use App\Models\Session;

Session::start();

// Only redirect if already logged in and not trying to login
if (Session::isLoggedIn() && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit();
}

include 'header.php'; 
?>

    <div class="container-fluid py-5">
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-lg-5">
                    <div id="login-container" class="bg-light rounded p-5">
                        <div class="section-title text-center position-relative mb-4">
                            <h6 class="d-inline-block position-relative text-secondary text-uppercase pb-2">Login</h6>
                            <h1 class="display-4">Welcome Back</h1>
                        </div>
                        
                        <div id="login-response"></div>
                        
                        <form hx-post="/youdemy/src/helper/signup.php" 
                              hx-target="#login-response" 
                              hx-swap="innerHTML"
                              hx-indicator="#loading">
                            <div id="loading" class="htmx-indicator">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="sr-only">Loading...</span>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <input type="email" name="email" class="form-control border-0 p-4" placeholder="Your Email" required />
                            </div>
                            <div class="form-group">
                                <input type="password" name="password" class="form-control border-0 p-4" placeholder="Your Password" required />
                            </div>
                            <div>
                                <button class="btn btn-primary btn-block py-3" type="submit">Login Now</button>
                            </div>
                            <div class="text-center mt-2">
                                <span class="text-secondary">Don't have an account? </span>
                                <a href="register.php" class="text-primary">Register Now</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
    .htmx-indicator { display:none; }
    .htmx-request .htmx-indicator { display:block; }
    .htmx-request.htmx-indicator { display:block; }
    </style>

    <script src="js/signup.js"></script>

<?php include 'footer.php'; ?>