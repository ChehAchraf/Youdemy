<?php include 'header.php'; ?>

    <!-- Register Start -->
    <div class="container-fluid py-5">
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-lg-7">
                    <div class="bg-light rounded p-5">
                        <div class="section-title text-center position-relative mb-4">
                            <h6 class="d-inline-block position-relative text-secondary text-uppercase pb-2">Registration</h6>
                            <h1 class="display-4">Create Account</h1>
                        </div>
                        <form>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <input type="text" class="form-control border-0 p-4" placeholder="First Name" required="required" />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <input type="text" class="form-control border-0 p-4" placeholder="Last Name" required="required" />
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <input type="email" class="form-control border-0 p-4" placeholder="Your Email" required="required" />
                            </div>
                            <div class="form-group">
                                <input type="tel" class="form-control border-0 p-4" placeholder="Phone Number" required="required" />
                            </div>
                            <div class="form-group">
                                <select class="form-control border-0 p-4 " required="required">
                                    <option value="" selected disabled>Select your role</option>
                                    <option value="student">Student</option>
                                    <option value="teacher">Teacher</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <input type="password" class="form-control border-0 p-4" placeholder="Password" required="required" />
                            </div>
                            <div class="form-group">
                                <input type="password" class="form-control border-0 p-4" placeholder="Confirm Password" required="required" />
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="agree" required="required">
                                    <label class="custom-control-label" for="agree">I agree to the Terms & Conditions</label>
                                </div>
                            </div>
                            <div>
                                <button class="btn btn-primary btn-block py-3" type="submit">Register Now</button>
                            </div>
                            <div class="text-center mt-4">
                                <span class="text-secondary">Already have an account? </span>
                                <a href="login.html" class="text-primary">Login</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Register End -->

<?php include 'footer.php'; ?>