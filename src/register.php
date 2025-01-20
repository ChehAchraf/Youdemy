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
                          hx-target="#signup-response">
                        
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
                        <div class="form-group">
                            <select name="role" class="form-control border-0 p-4" required>
                                <option value="">Select Role</option>
                                <option value="student">Student</option>
                                <option value="teacher">Teacher</option>
                            </select>
                        </div>
                        <!-- Teacher-specific fields (hidden by default) -->
                        <div id="teacher-fields" style="display: none;">
                            <div class="form-group">
                                <input type="text" name="specialization" class="form-control border-0 p-4" placeholder="Specialization (e.g., Web Development, Data Science)" />
                            </div>
                            <div class="alert alert-info">
                                <small>Note: Teacher accounts require admin approval before you can start creating courses.</small>
                            </div>
                        </div>
                        <div>
                            <button class="btn btn-primary btn-block py-3" type="submit">Sign Up Now</button>
                        </div>
                        <div class="text-center mt-2">
                            <span class="text-secondary">Already have an account? </span>
                            <a href="login.php" class="text-primary">Login</a>
                        </div>
                    </form>

                    <script>
                    document.querySelector('select[name="role"]').addEventListener('change', function() {
                        const teacherFields = document.getElementById('teacher-fields');
                        const specializationInput = document.querySelector('input[name="specialization"]');
                        
                        if (this.value === 'teacher') {
                            teacherFields.style.display = 'block';
                            specializationInput.required = true;
                        } else {
                            teacherFields.style.display = 'none';
                            specializationInput.required = false;
                        }
                    });

                    // Add event listeners for HTMX events
                    document.body.addEventListener('htmx:beforeRequest', function(evt) {
                        console.log('Sending request...', evt.detail);
                    });

                    document.body.addEventListener('htmx:afterRequest', function(evt) {
                        console.log('Response received:', evt.detail);
                        
                        if (evt.detail.failed) {
                            console.error('Request failed:', evt.detail.xhr.response);
                            document.getElementById('signup-response').innerHTML = 
                                '<div class="alert alert-danger">Registration failed. Please try again.</div>';
                            return;
                        }

                        try {
                            const response = JSON.parse(evt.detail.xhr.response);
                            document.getElementById('signup-response').innerHTML = response.message;
                            
                            if (response.success) {
                                setTimeout(() => {
                                    window.location.href = 'login.php';
                                }, 2000);
                            }
                        } catch (error) {
                            console.error('Error parsing response:', error);
                            document.getElementById('signup-response').innerHTML = 
                                '<div class="alert alert-danger">An unexpected error occurred. Please try again.</div>';
                        }
                    });

                    document.body.addEventListener('htmx:responseError', function(evt) {
                        console.error('Response error:', evt.detail);
                        document.getElementById('signup-response').innerHTML = 
                            '<div class="alert alert-danger">An error occurred while processing your request. Please try again.</div>';
                    });
                    </script>
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