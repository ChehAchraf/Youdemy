<?php 
require_once __DIR__ . '/../vendor/autoload.php';
use App\Models\Session;

Session::start();

// Redirect if already logged in
if (Session::isLoggedIn()) {
    header('Location: index.php');
    exit();
}

include 'header.php'; 
?>

<div class="container-fluid py-5">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-5">
                <div class="bg-light rounded p-5">
                    <div class="section-title text-center position-relative mb-4">
                        <h6 class="d-inline-block position-relative text-secondary text-uppercase pb-2">Register</h6>
                        <h1 class="display-4">Create Account</h1>
                    </div>
                    
                    <div id="signup-response"></div>
                    
                    <form hx-post="helper/register.php" 
                          hx-target="#signup-response" 
                          hx-swap="innerHTML"
                          hx-indicator="#loading">
                        
                        <div id="loading" class="htmx-indicator">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </div>

                        <div class="form-group">
                            <input type="text" name="firstname" class="form-control border-0 p-4" placeholder="First Name" required />
                        </div>
                        <div class="form-group">
                            <input type="text" name="lastname" class="form-control border-0 p-4" placeholder="Last Name" required />
                        </div>
                        <div class="form-group">
                            <input type="email" name="email" class="form-control border-0 p-4" placeholder="Your Email" required />
                        </div>
                        <div class="form-group">
                            <input type="password" name="password" class="form-control border-0 p-4" placeholder="Password" required />
                        </div>
                        <div class="form-group">
                            <input type="password" name="confirm_password" class="form-control border-0 p-4" placeholder="Confirm Password" required />
                        </div>
                        <div>
                            <button class="btn btn-primary btn-block py-3" type="submit">Sign Up Now</button>
                        </div>
                        <div class="text-center mt-2">
                            <span class="text-secondary">Already have an account? </span>
                            <a href="login.php" class="text-primary">Login</a>
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

<?php include 'footer.php'; ?>