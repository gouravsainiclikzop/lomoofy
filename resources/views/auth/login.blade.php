<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <meta name="format-detection" content="telephone=no"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign In - {{ config('app.name') }}</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('assets/images/favicon.png') }}"/>
    
    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,300i,400,400i,500,500i,700,700i,900,900i"/>
    
    <!-- CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap/css/bootstrap.ltr.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}"/>
</head>
<body>
    <div class="min-h-100 p-0 p-sm-6 d-flex align-items-stretch">
        <div class="card w-25x flex-grow-1 flex-sm-grow-0 m-sm-auto">
            <div class="card-body p-sm-5 m-sm-3 flex-grow-0">
                <h1 class="mb-0 fs-3">Sign In</h1>
                <div class="fs-exact-14 text-muted mt-2 pt-1 mb-5 pb-2">Log in to your account to continue.</div>
                
                <!-- Error Messages -->
                <div id="errorMessage"></div>
                
                <!-- Login Form -->
                <form id="loginForm">
                    <div class="mb-4">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control form-control-lg" id="email"   required/>
                        <div class="invalid-feedback" id="emailError"></div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control form-control-lg" id="password"   required/>
                        <div class="invalid-feedback" id="passwordError"></div>
                    </div>
                    
                    <div class="mb-4 row py-2 flex-wrap">
                        <div class="col-auto me-auto">
                            <label class="form-check mb-0">
                                <input type="checkbox" name="remember" class="form-check-input"/>
                                <span class="form-check-label">Remember me</span>
                            </label>
                        </div>
                    </div>
                    
                    <div>
                        <button type="submit" class="btn btn-primary btn-lg w-100" id="loginBtn">
                            <span id="loginBtnText">Sign In</span>
                            <span id="loginBtnSpinner" class="spinner-border spinner-border-sm d-none" role="status"></span>
                        </button>
                    </div>
                </form>
            </div>
            
           
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="{{ asset('assets/vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    
    <script>
        $(document).ready(function() {
            // CSRF Token Setup
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            
            // Login Form Submit
            $('#loginForm').on('submit', function(e) {
                e.preventDefault();
                
                // Clear previous errors
                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').text('');
                $('#errorMessage').empty();
                
                // Show loading state
                $('#loginBtnText').addClass('d-none');
                $('#loginBtnSpinner').removeClass('d-none');
                $('#loginBtn').prop('disabled', true);
                
                // Get form data
                let formData = {
                    email: $('#email').val(),
                    password: $('#password').val(),
                    remember: $('input[name="remember"]').is(':checked')
                };
                
                // AJAX Login Request
                $.ajax({
                    url: '{{ route("admin.login.post") }}',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if(response.success) {
                            // Redirect to dashboard
                            window.location.href = '{{ route("dashboard") }}';
                        }
                    },
                    error: function(xhr) {
                        // Hide loading state
                        $('#loginBtnText').removeClass('d-none');
                        $('#loginBtnSpinner').addClass('d-none');
                        $('#loginBtn').prop('disabled', false);
                        
                        if(xhr.status === 422) {
                            // Validation errors
                            let errors = xhr.responseJSON.errors;
                            if(errors.email) {
                                $('#email').addClass('is-invalid');
                                $('#emailError').text(errors.email[0]);
                            }
                            if(errors.password) {
                                $('#password').addClass('is-invalid');
                                $('#passwordError').text(errors.password[0]);
                            }
                        } else if(xhr.status === 401) {
                            // Invalid credentials
                            let message = xhr.responseJSON.message || 'Invalid credentials';
                            $('#errorMessage').html(`
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <strong>Error!</strong> ${message}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            `);
                        } else {
                            // Other errors
                            $('#errorMessage').html(`
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <strong>Error!</strong> An error occurred. Please try again.
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            `);
                        }
                    }
                });
            });
        });
    </script>
</body>
</html>

