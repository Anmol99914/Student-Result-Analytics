<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Portal - Student Result Analytics</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <style>
        :root {
            --admin-primary: #0073e6;
            --admin-secondary: #0059b8;
            --admin-light: #e6f2ff;
        }
        
        body { 
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
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
            box-shadow: 0 15px 35px rgba(0, 115, 230, 0.15);
            border: 1px solid rgba(0, 115, 230, 0.1);
            overflow: hidden;
        }
        
        .admin-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, var(--admin-primary) 0%, var(--admin-secondary) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: -50px auto 30px;
            box-shadow: 0 10px 25px rgba(0, 115, 230, 0.3);
            border: 5px solid white;
            position: relative;
        }
        
        .admin-icon:after {
            content: '';
            position: absolute;
            top: -5px;
            left: -5px;
            right: -5px;
            bottom: -5px;
            background: linear-gradient(135deg, var(--admin-primary) 0%, var(--admin-secondary) 100%);
            border-radius: 50%;
            z-index: -1;
            opacity: 0.15;
        }
        
        .form-control {
            border: 2px solid #e1e8f0;
            border-radius: 12px;
            padding: 14px 18px;
            font-size: 1.05rem;
            transition: all 0.3s ease;
            background: #f8fafc;
        }
        
        .form-control:focus {
            border-color: var(--admin-primary);
            box-shadow: 0 0 0 4px rgba(0, 115, 230, 0.1);
            background: white;
            transform: translateY(-1px);
        }
        
        .form-label {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 10px;
            font-size: 0.95rem;
            letter-spacing: 0.3px;
        }
        
        .btn-admin {
            background: linear-gradient(135deg, var(--admin-primary) 0%, var(--admin-secondary) 100%);
            border: none;
            border-radius: 12px;
            padding: 16px;
            font-weight: 600;
            font-size: 1.1rem;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            box-shadow: 0 6px 20px rgba(0, 115, 230, 0.25);
        }
        
        .btn-admin:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(0, 115, 230, 0.35);
            background: linear-gradient(135deg, var(--admin-secondary) 0%, var(--admin-primary) 100%);
        }
        
        .btn-admin:active {
            transform: translateY(-1px);
        }
        
        .floating-label {
            position: relative;
            margin-bottom: 25px;
        }
        
        .floating-label .form-control:focus ~ .floating-text,
        .floating-label .form-control:not(:placeholder-shown) ~ .floating-text {
            transform: translateY(-28px) scale(0.85);
            color: var(--admin-primary);
            font-weight: 600;
            background: white;
            padding: 0 10px;
            left: 10px;
        }
        
        .floating-text {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            background: #f8fafc;
            padding: 0 8px;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            pointer-events: none;
            color: #64748b;
            font-size: 1.05rem;
        }
        
        .security-badge {
            position: absolute;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 6px 15px;
            border-radius: 25px;
            font-size: 0.85rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);
        }
        
        .header-text {
            text-align: center;
            margin-bottom: 35px;
        }
        
        .header-text h2 {
            color: #1a202c;
            font-weight: 700;
            font-size: 2.2rem;
            margin-bottom: 8px;
            background: linear-gradient(135deg, var(--admin-primary) 0%, var(--admin-secondary) 100%);
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
            background-color: var(--admin-primary);
            border-color: var(--admin-primary);
        }
        
        .form-check-input:focus {
            border-color: var(--admin-primary);
            box-shadow: 0 0 0 3px rgba(0, 115, 230, 0.25);
        }
        
        .help-link {
            color: var(--admin-primary);
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        
        .help-link:hover {
            color: var(--admin-secondary);
            text-decoration: underline;
        }
        
        .admin-only-notice {
            background: linear-gradient(135deg, var(--admin-light) 0%, #f0f7ff 100%);
            border: 1px solid rgba(0, 115, 230, 0.15);
            border-radius: 12px;
            padding: 15px;
            text-align: center;
            margin-top: 30px;
        }
        
        .copyright {
            color: #718096;
            font-size: 0.9rem;
            margin-top: 40px;
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }
        
        .copyright a {
            color: var(--admin-primary);
            text-decoration: none;
            font-weight: 500;
        }
        
        .copyright a:hover {
            color: var(--admin-secondary);
            text-decoration: underline;
        }
        
        .back-home-btn {
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid #e2e8f0;
            color: var(--admin-primary);
            font-weight: 500;
            padding: 10px 20px;
            border-radius: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        
        .back-home-btn:hover {
            background: white;
            border-color: var(--admin-primary);
            color: var(--admin-primary);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 115, 230, 0.15);
        }
        
        @media (max-width: 768px) {
            .login-wrapper {
                max-width: 90%;
            }
            
            .admin-icon {
                width: 90px;
                height: 90px;
                margin-top: -45px;
            }
            
            .header-text h2 {
                font-size: 1.8rem;
            }
        }

        /* fix */
        /* Floating label container */
.floating-label {
    position: relative;
    margin-bottom: 25px;
}

/* Input field styling */
.floating-label .form-control {
    padding: 14px 18px;
    height: auto;
    background: #f8fafc !important;
}

/* When input has value or is focused - move label up LESS */
.floating-label .form-control:focus ~ .floating-text,
.floating-label .form-control:not(:placeholder-shown) ~ .floating-text {
    transform: translateY(-25px) scale(0.9); /* Changed from -35px to -25px */
    color: var(--admin-primary);
    font-weight: 600;
    background: white;
    padding: 0 8px;
    left: 15px;
    top: 30px; /* Adjusted from 35px to 30px */
}

/* Default label position - INSIDE the input */
.floating-text {
    position: absolute;
    left: 18px;
    top: 14px;
    transform: none;
    background: transparent;
    padding: 0 5px;
    transition: all 0.2s ease;
    pointer-events: none;
    color: #64748b;
    font-size: 1rem;
    line-height: 1.5;
    z-index: 1;
}

/* Label when moved up - with white background */
.floating-label .form-control:focus ~ .floating-text,
.floating-label .form-control:not(:placeholder-shown) ~ .floating-text {
    background: white;
    top: -2px; /* Changed from -8px to -2px (moves down) */
    left: 15px;
    font-size: 0.9rem;
    padding: 0 6px;
    border-left: 2px solid var(--admin-primary);
    border-right: 2px solid var(--admin-primary);
    border-radius: 4px;
}

/* Ensure input text is always visible */
.form-control {
    position: relative;
    z-index: 2;
}

/* Keep background white on focus */
.form-control:focus {
    background: white !important;
}

/* Keep placeholder visible */
.floating-label .form-control::placeholder {
    color: #a0aec0;
    opacity: 1;
    transition: opacity 0.2s ease;
}

/* When input is focused or has value, fade the placeholder */
.floating-label .form-control:focus::placeholder,
.floating-label .form-control:not(:placeholder-shown)::placeholder {
    opacity: 0.5;
}

/* Ensure placeholder is always visible enough */
.floating-label .form-control::placeholder {
    color: #a0aec0;
    font-size: 1rem;
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
            
            <!-- Admin Icon -->
            <div class="admin-icon">
                <i class="bi bi-shield-lock text-white fs-2"></i>
            </div>
            
            <!-- Page heading -->
            <div class="header-text">
                <h2>Administrator Portal</h2>
            </div>

                    <?php
        // Error messages
        $error_msg = '';
        $error_type = $_GET['error'] ?? '';

        if ($error_type === "invalid") {
            $error_msg = "Invalid administrator credentials!";
        } elseif ($error_type === "empty") {
            $error_msg = "Please fill in all fields.";
        } elseif ($error_type === "session_expired") {
            $error_msg = "Session expired. Please login again.";
        } elseif ($error_type === "unauthorized") {
            $error_msg = "Unauthorized access attempt!";
        }

        if($error_msg): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill fs-5 me-3"></i>
                <div>
                    <strong class="d-block">Authentication Failed</strong>
                    <span class="small"><?php echo $error_msg; ?></span>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

            <!-- Login Form -->
            <form action="admin_validation.php" method="POST" name="admin_form" id="adminForm" class="needs-validation" novalidate>
                <div class="floating-label">
                    <input type="text" class="form-control" id="username" name="username" 
                           placeholder="Enter your username" required autocomplete="username">
                    <label class="floating-text" for="username">
                        <i class="bi bi-person-badge me-2"></i>Administrator Username
                    </label>
                    <div class="invalid-feedback">
                        Please enter your administrator username.
                    </div>
                </div>
                
                <div class="floating-label">
                    <input type="password" class="form-control" id="password" name="password" 
                           placeholder="Enter your password" required autocomplete="current-password">
                    <label class="floating-text" for="password">
                        <i class="bi bi-key me-2"></i>Password
                    </label>
                    <div class="invalid-feedback">
                        Please enter your password.
                    </div>
                </div>
                
                
                <button type="submit" class="btn btn-admin w-100" id="loginBtn">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Access Dashboard
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
        // Enhanced form handling with floating labels
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('adminForm');
            const loginBtn = document.getElementById('loginBtn');
            
            // Initialize floating labels
            const floatingInputs = document.querySelectorAll('.floating-label .form-control');
            floatingInputs.forEach(input => {
                if (input.value.trim() !== '') {
                    input.nextElementSibling.classList.add('active');
                }
                
                input.addEventListener('focus', function() {
                    this.nextElementSibling.classList.add('active');
                });
                
                input.addEventListener('blur', function() {
                    if (this.value.trim() === '') {
                        this.nextElementSibling.classList.remove('active');
                    }
                });
            });
            
            // Auto-focus on username
            document.getElementById('username').focus();
            
            // Form validation
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                if (!form.checkValidity()) {
                    e.stopPropagation();
                    form.classList.add('was-validated');
                    return;
                }
                
                // Show loading state
                const originalText = loginBtn.innerHTML;
                loginBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Verifying Credentials...';
                loginBtn.disabled = true;
                
                form.submit();

               
        
        // Handle back button cache
        window.addEventListener('pageshow', function (event) {
            if (event.persisted) {
                window.location.reload();
            }
        });
    </script>

</body>

</html>