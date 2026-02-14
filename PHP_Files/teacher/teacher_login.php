
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Teacher Portal - Student Result Analytics</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <style>
        :root {
            --teacher-primary: #ff6b35;
            --teacher-secondary: #e55a2b;
            --teacher-light: #fff3e0;
        }
        
        body { 
            background: linear-gradient(135deg, #f9fafb 0%, #f1f3f4 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }
        
        .login-wrapper {
            max-width: 500px;
            width: 100%;
            margin: 0 auto;
        }
        
        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(255, 107, 53, 0.12);
            border: 1px solid rgba(255, 107, 53, 0.08);
            overflow: hidden;
        }
        
        .teacher-icon {
            width: 90px;
            height: 90px;
            background: linear-gradient(135deg, var(--teacher-primary) 0%, var(--teacher-secondary) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: -45px auto 25px;
            box-shadow: 0 8px 20px rgba(255, 107, 53, 0.25);
            border: 4px solid white;
            position: relative;
        }
        
        .teacher-icon:after {
            content: '';
            position: absolute;
            top: -4px;
            left: -4px;
            right: -4px;
            bottom: -4px;
            background: linear-gradient(135deg, var(--teacher-primary) 0%, var(--teacher-secondary) 100%);
            border-radius: 50%;
            z-index: -1;
            opacity: 0.12;
        }
        
        .form-control {
            border: 2px solid #e0e6ed;
            border-radius: 12px;
            padding: 14px 18px;
            font-size: 1.05rem;
            transition: all 0.3s ease;
            background: #f9f9f9;
        }
        
        .form-control:focus {
            border-color: var(--teacher-primary);
            box-shadow: 0 0 0 4px rgba(255, 107, 53, 0.1);
            background: white;
            transform: translateY(-1px);
        }
        
        .btn-teacher {
            background: linear-gradient(135deg, var(--teacher-primary) 0%, var(--teacher-secondary) 100%);
            border: none;
            border-radius: 12px;
            padding: 16px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            box-shadow: 0 6px 20px rgba(255, 107, 53, 0.2);
        }
        
        .btn-teacher:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(255, 107, 53, 0.3);
            background: linear-gradient(135deg, var(--teacher-secondary) 0%, var(--teacher-primary) 100%);
        }
        
        .btn-teacher:active {
            transform: translateY(-1px);
        }
        
        .floating-label {
            position: relative;
            margin-bottom: 25px;
        }
        
        /* FIXED: Updated floating label CSS - labels stay attached to input */
        .floating-label .form-control:focus,
        .floating-label .form-control:not(:placeholder-shown) {
            padding-top: 22px;
            padding-bottom: 8px;
        }
        
        .floating-label .form-control:focus ~ .floating-text,
        .floating-label .form-control:not(:placeholder-shown) ~ .floating-text,
        .floating-label .form-control.has-value ~ .floating-text {
            transform: translateY(-15px) scale(0.85);
            color: var(--teacher-primary);
            font-weight: 600;
            background: #f9f9f9;
            padding: 0 6px;
            left: 12px;
            top: 18px;
            border-radius: 4px;
            z-index: 5;
            pointer-events: none;
            border-left: 2px solid var(--teacher-primary);
            border-right: 2px solid var(--teacher-primary);
        }
        
        .floating-label .form-control:focus ~ .floating-text {
            background: white;
            border-left: 3px solid var(--teacher-primary);
            border-right: 3px solid var(--teacher-primary);
        }
        
        .floating-text {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            background: #f9f9f9;
            padding: 0 8px;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            pointer-events: none;
            color: #64748b;
            font-size: 1.05rem;
            z-index: 2;
        }
        
        .role-badge {
            position: absolute;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, #495057 0%, #343a40 100%);
            color: white;
            padding: 6px 15px;
            border-radius: 25px;
            font-size: 0.85rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 12px rgba(73, 80, 87, 0.2);
        }
        
        .header-text {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .header-text h2 {
            color: #1a202c;
            font-weight: 700;
            font-size: 2rem;
            margin-bottom: 8px;
            background: linear-gradient(135deg, var(--teacher-primary) 0%, var(--teacher-secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .header-text p {
            color: #718096;
            font-size: 1.1rem;
            font-weight: 400;
        }
        
        .form-check-input:checked {
            background-color: var(--teacher-primary);
            border-color: var(--teacher-primary);
        }
        
        .form-check-input:focus {
            border-color: var(--teacher-primary);
            box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.25);
        }
        
        .help-links {
            color: var(--teacher-primary);
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        
        .help-links:hover {
            color: var(--teacher-secondary);
            text-decoration: underline;
        }
        
        .teacher-notice {
            background: linear-gradient(135deg, var(--teacher-light) 0%, #fff8f0 100%);
            border: 1px solid rgba(255, 107, 53, 0.12);
            border-radius: 12px;
            padding: 15px;
            text-align: center;
            margin-top: 25px;
        }
        
        .copyright {
            color: #718096;
            font-size: 0.9rem;
            margin-top: 35px;
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }
        
        .copyright a {
            color: var(--teacher-primary);
            text-decoration: none;
            font-weight: 500;
        }
        
        .copyright a:hover {
            color: var(--teacher-secondary);
            text-decoration: underline;
        }
        
        .back-home-btn {
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid #e2e8f0;
            color: var(--teacher-primary);
            font-weight: 500;
            padding: 10px 20px;
            border-radius: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        
        .back-home-btn:hover {
            background: white;
            border-color: var(--teacher-primary);
            color: var(--teacher-primary);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255, 107, 53, 0.15);
        }
        
        .teacher-features {
            background: linear-gradient(135deg, #f0f7ff 0%, #e6f0ff 100%);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 25px;
            border-left: 5px solid var(--teacher-primary);
        }
        
        .feature-list {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-top: 10px;
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            color: #495057;
        }
        
        .feature-item i {
            color: var(--teacher-primary);
            font-size: 0.9rem;
        }
        
        /* Email hint styling */
        .email-hint {
            display: block;
            margin-top: 5px;
            margin-left: 5px;
            font-size: 0.85rem;
            color: #718096;
            transition: all 0.3s ease;
        }
        
        .floating-label .form-control:focus ~ .email-hint,
        .floating-label .form-control:not(:placeholder-shown) ~ .email-hint {
            opacity: 0.8;
            transform: translateY(2px);
        }
        
        @media (max-width: 768px) {
            .login-wrapper {
                max-width: 90%;
                padding: 0 15px;
            }
            
            .teacher-icon {
                width: 80px;
                height: 80px;
                margin-top: -40px;
            }
            
            .header-text h2 {
                font-size: 1.7rem;
            }
            
            .teacher-features {
                padding: 15px;
            }
            
            .feature-list {
                grid-template-columns: 1fr;
            }
            
            .floating-label .form-control:focus ~ .floating-text,
            .floating-label .form-control:not(:placeholder-shown) ~ .floating-text {
                transform: translateY(-14px) scale(0.8);
            }
        }
    </style>
</head>

<body class="d-flex flex-column align-items-center justify-content-center py-5">

    <!-- Back to Home -->
    <a href="../../index.html" class="btn back-home-btn position-fixed" 
       style="top: 25px; left: 25px; z-index: 1000;">
        <i class="bi bi-arrow-left me-2"></i>Back to Home
    </a>

    <!-- Main Container -->
    <div class="login-wrapper">
        <div class="login-container p-5 position-relative">
            <!-- Role Badge -->
            <div class="role-badge">
                <i class="bi bi-person-badge me-2"></i>Teacher Portal
            </div>
            
            <!-- Teacher Icon -->
            <div class="teacher-icon">
                <i class="bi bi-person-workspace text-white fs-2"></i>
            </div>
            
            <!-- Page heading -->
            <div class="header-text">
                <h2>Teacher Portal</h2>
                <p>Academic Management System</p>
            </div>

            <!-- Teacher Features -->
            <div class="teacher-features">
                <div class="d-flex align-items-start">
                    <i class="bi bi-tools fs-5 me-3" style="color: var(--teacher-primary);"></i>
                    <div>
                        <h6 class="mb-2" style="color: var(--teacher-secondary);">Teacher Tools</h6>
                        <div class="feature-list">
                            <div class="feature-item">
                                <i class="bi bi-pencil-square"></i>
                                <span>Enter Marks</span>
                            </div>
                            <div class="feature-item">
                                <i class="bi bi-clipboard-data"></i>
                                <span>View Reports</span>
                            </div>
                            <div class="feature-item">
                                <i class="bi bi-people"></i>
                                <span>Manage Students</span>
                            </div>
                            <div class="feature-item">
                                <i class="bi bi-calendar-check"></i>
                                <span>Academic Calendar</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php
            // Error messages
            $error_msg = '';
            if(isset($_GET['error'])){
                if($_GET['error'] === "invalid"){
                    $error_msg = "Invalid email or password!";
                } elseif ($_GET['error'] === "inactive") {
                    $error_msg = "Your account is inactive. Please contact administration.";
                } elseif ($_GET['error'] === "empty") {
                    $error_msg = "Please fill in all fields.";
                }
            }
            
            if($error_msg): ?>
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bi bi-exclamation-triangle-fill fs-5 me-3"></i>
                    <div>
                        <strong class="d-block">Login Failed</strong>
                        <span class="small"><?php echo $error_msg; ?></span>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form action="teacher_validation.php" method="POST" name="teacher_form" id="teacherForm" class="needs-validation" novalidate>
                <div class="floating-label">
                    <input type="text" class="form-control" id="email" name="email" 
                           placeholder="Enter your email address">
                    <label class="floating-text" for="email">
                        <i class="bi bi-envelope me-2"></i>Teacher Email
                    </label>
                    <span class="email-hint">e.g., name@college.com</span>
                    <div class="invalid-feedback">
                        Please enter your teacher email address.
                    </div>
                </div>
                
                <div class="floating-label">
                    <input type="password" class="form-control" id="password" name="password" 
                           placeholder="Enter your password">
                    <label class="floating-text" for="password">
                        <i class="bi bi-key me-2"></i>Password
                    </label>
                    <div class="invalid-feedback">
                        Please enter your password.
                    </div>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" id="remember" name="remember">
                        <label class="form-check-label text-muted" for="remember">
                            <i class="bi bi-clock-history me-1"></i>Keep me logged in
                        </label>
                    </div>
                    <a href="#" class="help-links small">
                        <i class="bi bi-question-circle me-1"></i>Forgot password?
                    </a>
                </div>
                
                <button type="submit" class="btn btn-teacher w-100" id="loginBtn">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Access Teacher Dashboard
                </button>
            </form>
            
            <!-- Copyright -->
            <div class="copyright">
                <p class="mb-2">© 2025 Student Result Analytics System</p>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('teacherForm');
        const loginBtn = document.getElementById('loginBtn');
        
        // Initialize floating labels
        const floatingInputs = document.querySelectorAll('.floating-label .form-control');
        floatingInputs.forEach(input => {
            // Set placeholder to single space for floating label effect
            input.setAttribute('placeholder', ' ');
            
            // Check on page load if input has value
            if (input.value.trim() !== '') {
                input.classList.add('has-value');
            }
            
            // Handle input events
            input.addEventListener('input', function() {
                if (this.value.trim() !== '') {
                    this.classList.add('has-value');
                } else {
                    this.classList.remove('has-value');
                }
            });
        });
        
        // Auto-focus on email field
        document.getElementById('email').focus();
        
        // Remove Bootstrap's default validation styling
        form.classList.remove('was-validated');
        
        // Form submission - ONLY validate here
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Remove any existing validation classes
            document.getElementById('email').classList.remove('is-invalid', 'is-valid');
            document.getElementById('password').classList.remove('is-invalid', 'is-valid');
            
            // Get values
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value.trim();
            
            // Manual validation
            let isValid = true;
            
            if (email === '') {
                document.getElementById('email').classList.add('is-invalid');
                isValid = false;
            }
            
            if (password === '') {
                document.getElementById('password').classList.add('is-invalid');
                isValid = false;
            }
            
            if (!isValid) {
                return; // Don't add was-validated class
            }
            
            // Show loading state
            const originalText = loginBtn.innerHTML;
            loginBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Authenticating...';
            loginBtn.disabled = true;
            
            // Submit form
            form.submit();
        });
    });

    window.addEventListener('pageshow', function (event) {
            if (event.persisted) {
                window.location.reload();
            }
        });
</script>

</body>

</html>