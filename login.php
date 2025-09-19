<?php
require_once 'config/config.php';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    try {
        // Get JSON input
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['email']) || !isset($input['password'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid input data']);
            exit;
        }
        
        $email = sanitize_input($input['email']);
        $password = $input['password'];
        
        // Validate input
        if (empty($email) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Email and password are required']);
            exit;
        }
        
        if (!validate_email($email)) {
            echo json_encode(['success' => false, 'message' => 'Please enter a valid email address']);
            exit;
        }
        
        // Check user credentials
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Successful login
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['fullName'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['last_activity'] = time();
            
            // Determine redirect URL based on role
            $redirect = ($user['role'] === 'admin') ? 'admin_dashboard.php' : 'home.php';
            
            echo json_encode([
                'success' => true, 
                'message' => 'Login successful',
                'redirect' => $redirect,
                'user' => [
                    'name' => $user['fullName'],
                    'role' => $user['role']
                ]
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
        }
        
    } catch (Exception $e) {
        error_log("Login error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
    }
    exit;
}

// If user is already logged in, redirect to appropriate page
if (is_logged_in()) {
    $redirect = is_admin() ? 'admin_dashboard.php' : 'home.php';
    redirect($redirect);
}
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin="" />
    <link
      rel="stylesheet"
      as="style"
      onload="this.rel='stylesheet'"
      href="https://fonts.googleapis.com/css2?display=swap&amp;family=Noto+Sans%3Awght%40400%3B500%3B700%3B900&amp;family=Work+Sans%3Awght%40400%3B500%3B700%3B900"
    />

    <title>Login - <?php echo SITE_NAME; ?></title>
    <link rel="icon" type="image/x-icon" href="data:image/x-icon;base64," />

    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
</head>
<body>
    <div class="relative flex size-full min-h-screen flex-col bg-white group/design-root overflow-x-hidden" style='font-family: "Work Sans", "Noto Sans", sans-serif;'>
        <div class="layout-container flex h-full grow flex-col">
            <header class="flex items-center justify-between whitespace-nowrap border-b border-solid border-b-[#f4f0f0] px-10 py-3">
                <div class="flex items-center gap-4 text-[#181111]">
                    <div class="size-4">
                        <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M24 4H42V17.3333V30.6667H24V44H6V30.6667V17.3333H24V4Z" fill="currentColor"></path>
                        </svg>
                    </div>
                    <h2 class="text-[#181111] text-lg font-bold leading-tight tracking-[-0.015em]"><?php echo SITE_NAME; ?></h2>
                </div>
            </header>
            <div class="px-40 flex flex-1 justify-center py-5">
                <div class="layout-content-container flex flex-col w-[512px] max-w-[512px] py-5 max-w-[960px] flex-1">
                    <h2 class="text-[#181111] tracking-light text-[28px] font-bold leading-tight px-4 text-center pb-3 pt-5">Welcome back</h2>
                    <div class="flex max-w-[480px] flex-wrap items-end gap-4 px-4 py-3">
                        <label class="flex flex-col min-w-40 flex-1">
                            <p class="text-[#181111] text-base font-medium leading-normal pb-2">Email</p>
                            <input
                              id="emailInput"
                              placeholder="Enter your email"
                              class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-xl text-[#181111] focus:outline-0 focus:ring-0 border border-[#e5dcdc] bg-white focus:border-[#e5dcdc] h-14 placeholder:text-[#886364] p-[15px] text-base font-normal leading-normal"
                            />
                        </label>
                    </div>
                    <div class="flex max-w-[480px] flex-wrap items-end gap-4 px-4 py-3">
                        <label class="flex flex-col min-w-40 flex-1">
                            <p class="text-[#181111] text-base font-medium leading-normal pb-2">Password</p>
                            <input
                              id="passwordInput"
                              type="password"
                              placeholder="Enter your password"
                              class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-xl text-[#181111] focus:outline-0 focus:ring-0 border border-[#e5dcdc] bg-white focus:border-[#e5dcdc] h-14 placeholder:text-[#886364] p-[15px] text-base font-normal leading-normal"
                            />
                        </label>
                    </div>
                    <div id="errorMsg" class="text-red-600 text-sm font-medium px-4 py-2"></div>
                    <p class="text-[#886364] text-sm font-normal leading-normal pb-3 pt-1 px-4 underline cursor-pointer" onclick="alert('Please contact administrator for password reset')">Forgot Password?</p>
                    <div class="flex px-4 py-3">
                        <button
                          id="loginBtn"
                          class="flex min-w-[84px] max-w-[480px] cursor-pointer items-center justify-center overflow-hidden rounded-full h-12 px-5 flex-1 bg-[#e82630] text-white text-base font-bold leading-normal tracking-[0.015em]"
                        >
                          <span class="truncate">Login</span>
                        </button>
                    </div>
                    <p class="text-[#886364] text-sm font-normal leading-normal pb-3 pt-1 px-4 text-center">Or</p>
                    <div class="flex justify-center">
                        <div class="flex flex-1 gap-3 max-w-[480px] flex-col items-stretch px-4 py-3">
                            <button
                              id="signUpBtn"
                              class="flex min-w-[84px] max-w-[480px] cursor-pointer items-center justify-center overflow-hidden rounded-full h-10 px-4 bg-[#f4f0f0] text-[#181111] text-sm font-bold leading-normal tracking-[0.015em] w-full"
                            >
                              <span class="truncate">Sign up</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
      document.getElementById('loginBtn').addEventListener('click', function(e) {
        e.preventDefault();
        const email = document.getElementById('emailInput').value.trim();
        const password = document.getElementById('passwordInput').value;
        const errorMsg = document.getElementById('errorMsg');
        errorMsg.textContent = '';

        if (!email) {
          errorMsg.textContent = 'Email is required.';
          return;
        }
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
          errorMsg.textContent = 'Please enter a valid email address.';
          return;
        }
        if (!password) {
          errorMsg.textContent = 'Password is required.';
          return;
        }

        errorMsg.textContent = 'Logging in...';
        
        // Send login request to backend
        fetch('login.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            email: email,
            password: password
          })
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            window.location.href = data.redirect;
          } else {
            errorMsg.textContent = data.message;
          }
        })
        .catch(error => {
          console.error('Login error:', error);
          errorMsg.textContent = 'Login error. Please try again.';
        });
      });

      document.getElementById('signUpBtn').addEventListener('click', function(e) {
        e.preventDefault();
        window.location.href = 'register.php';
      });

      // Handle enter key press for login form
      document.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
          document.getElementById('loginBtn').click();
        }
      });
    </script>
</body>
</html>