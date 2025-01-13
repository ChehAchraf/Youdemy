<?php include 'header.php'; ?>

    <!-- Login Start -->
    <div class="container-fluid py-5">
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-lg-5">
                    <div class="bg-light rounded p-5">
                        <div class="section-title text-center position-relative mb-4">
                            <h6 class="d-inline-block position-relative text-secondary text-uppercase pb-2">Login</h6>
                            <h1 class="display-4">Welcome Back</h1>
                        </div>
                        <form>
                            <div class="form-group">
                                <input type="email" class="form-control border-0 p-4" placeholder="Your Email" required="required" />
                            </div>
                            <div class="form-group">
                                <input type="password" class="form-control border-0 p-4" placeholder="Your Password" required="required" />
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="remember">
                                    <label class="custom-control-label" for="remember">Remember me</label>
                                </div>
                            </div>
                            <div>
                                <button class="btn btn-primary btn-block py-3" type="submit">Login Now</button>
                            </div>
                            <div class="text-center mt-4">
                                <a href="#" class="text-secondary">Forgot Password?</a>
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
    <!-- Login End -->

<?php include 'footer.php'; ?>