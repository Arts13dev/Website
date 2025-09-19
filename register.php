<?php
require_once 'config/config.php';

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    try {
        $fullName = sanitize_input($_POST['fullName'] ?? '');
        $email = sanitize_input($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirmPassword'] ?? '';
        
        // Validate input
        if (empty($fullName) || empty($email) || empty($password) || empty($confirmPassword)) {
            echo json_encode(['success' => false, 'message' => 'All fields are required']);
            exit;
        }
        
        if (!validate_email($email)) {
            echo json_encode(['success' => false, 'message' => 'Please enter a valid email address']);
            exit;
        }
        
        if (strlen($password) < PASSWORD_MIN_LENGTH) {
            echo json_encode(['success' => false, 'message' => 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters long']);
            exit;
        }
        
        if ($password !== $confirmPassword) {
            echo json_encode(['success' => false, 'message' => 'Passwords do not match']);
            exit;
        }
        
        // Check if user already exists
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'An account with this email already exists']);
            exit;
        }
        
        // Create new user
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (fullName, email, password, role) VALUES (?, ?, ?, 'customer')");
        
        if ($stmt->execute([$fullName, $email, $hashedPassword])) {
            echo json_encode([
                'success' => true,
                'message' => 'Registration successful! You can now log in.'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Registration failed. Please try again.']);
        }
        
    } catch (Exception $e) {
        error_log("Registration error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
    }
    exit;
}

// If user is already logged in, redirect to home
if (is_logged_in()) {
    redirect('home.php');
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

    <title>Register - <?php echo SITE_NAME; ?></title>
    <link rel="icon" type="image/x-icon" href="data:image/x-icon;base64," />

    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
</head>
<body>
    <div
      class="relative flex size-full min-h-screen flex-col bg-white group/design-root overflow-x-hidden"
      style='--checkbox-tick-svg: url(&apos;data:image/svg+xml,%3csvg viewBox=%270 0 16 16%27 fill=%27rgb(255,255,255)%27 xmlns=%27http://www.w3.org/2000/svg%27%3e%3cpath d=%27M12.207 4.793a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0l-2-2a1 1 0 011.414-1.414L6.5 9.086l4.293-4.293a1 1 0 011.414 0z%27/%3e%3c/svg%3e&apos;); font-family: "Work Sans", "Noto Sans", sans-serif;'
    >
      <div class="layout-container flex h-full grow flex-col">
        <header class="flex items-center justify-between whitespace-nowrap border-b border-solid border-b-[#f4f0f0] px-10 py-3">
          <div class="flex items-center gap-4 text-[#181111]">
            <div class="size-4">
              <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 4H17.3334V17.3334H30.6666V30.6666H44V44H4V4Z" fill="currentColor"></path></svg>
            </div>
            <h2 class="text-[#181111] text-lg font-bold leading-tight tracking-[-0.015em]"><?php echo SITE_NAME; ?></h2>
          </div>
          <div class="flex flex-1 justify-end gap-8">
            <div class="flex items-center gap-9">
              <a id="homeBtn" class="text-[#181111] text-sm font-medium leading-normal" href="home.php">Home</a>
              <a id="shopBtn" class="text-[#181111] text-sm font-medium leading-normal" href="products.php">Shop</a>
              <a class="text-[#181111] text-sm font-medium leading-normal" href="about.php">About</a>
              <a class="text-[#181111] text-sm font-medium leading-normal" href="contact.php">Contact</a>
            </div>
            <div class="flex gap-2">
              <button
                id="loginBtn"
                class="flex min-w-[84px] max-w-[480px] cursor-pointer items-center justify-center overflow-hidden rounded-full h-10 px-4 bg-[#f4f0f0] text-[#181111] text-sm font-bold leading-normal tracking-[0.015em]"
              >
                <span class="truncate">Login</span>
              </button>
              <a
                href="cart.php"
                class="flex max-w-[480px] cursor-pointer items-center justify-center overflow-hidden rounded-full h-10 bg-[#f4f0f0] text-[#181111] gap-2 text-sm font-bold leading-normal tracking-[0.015em] min-w-0 px-2.5"
              >
                <div class="text-[#181111]" data-icon="ShoppingCart" data-size="20px" data-weight="regular">
                  <svg xmlns="http://www.w3.org/2000/svg" width="20px" height="20px" fill="currentColor" viewBox="0 0 256 256">
                    <path
                      d="M222.14,58.87A8,8,0,0,0,216,56H54.68L49.79,29.14A16,16,0,0,0,34.05,16H16a8,8,0,0,0,0,16h18L59.56,172.29a24,24,0,0,0,5.33,11.27,28,28,0,1,0,44.4,8.44h45.42A27.75,27.75,0,0,0,152,204a28,28,0,1,0,28-28H83.17a8,8,0,0,1-7.87-6.57L72.13,152h116a24,24,0,0,0,23.61-19.71l12.16-66.86A8,8,0,0,0,222.14,58.87ZM96,204a12,12,0,1,1-12-12A12,12,0,0,1,96,204Zm96,0a12,12,0,1,1-12-12A12,12,0,0,1,192,204Zm4-74.57A8,8,0,0,1,188.1,136H69.22L57.59,72H206.41Z"
                    ></path>
                  </svg>
                </div>
              </a>
            </div>
          </div>
        </header>
        <div class="flex flex-1 justify-center px-40 py-5">
          <div class="layout-content-container flex w-[512px] max-w-[512px] flex-col py-5 max-w-[960px] flex-1">
            <h2 class="text-[#181111] tracking-light px-4 pb-3 pt-5 text-center text-[28px] font-bold leading-tight">Create Your Account</h2>
            <form id="registerForm">
              <div class="flex max-w-[480px] flex-wrap items-end gap-4 px-4 py-3">
                <label class="flex min-w-40 flex-1 flex-col">
                  <p class="pb-2 text-base font-medium leading-normal text-[#181111]">Full Name</p>
                  <input
                    id="fullName"
                    name="fullName"
                    type="text"
                    placeholder="Enter your full name"
                    class="form-input flex h-14 min-w-0 flex-1 resize-none overflow-hidden rounded-xl border border-[#e5dcdc] bg-white p-[15px] text-base font-normal leading-normal text-[#181111] placeholder:text-[#886364] focus:border-[#e5dcdc] focus:outline-0 focus:ring-0"
                    required
                  />
                </label>
              </div>
              <div class="flex max-w-[480px] flex-wrap items-end gap-4 px-4 py-3">
                <label class="flex min-w-40 flex-1 flex-col">
                  <p class="pb-2 text-base font-medium leading-normal text-[#181111]">Email</p>
                  <input
                    id="email"
                    name="email"
                    type="email"
                    placeholder="Enter your email"
                    class="form-input flex h-14 min-w-0 flex-1 resize-none overflow-hidden rounded-xl border border-[#e5dcdc] bg-white p-[15px] text-base font-normal leading-normal text-[#181111] placeholder:text-[#886364] focus:border-[#e5dcdc] focus:outline-0 focus:ring-0"
                    required
                  />
                </label>
              </div>
              <div class="flex max-w-[480px] flex-wrap items-end gap-4 px-4 py-3">
                <label class="flex min-w-40 flex-1 flex-col">
                  <p class="pb-2 text-base font-medium leading-normal text-[#181111]">Password</p>
                  <input
                    id="password"
                    name="password"
                    type="password"
                    placeholder="Enter your password (min <?php echo PASSWORD_MIN_LENGTH; ?> characters)"
                    class="form-input flex h-14 min-w-0 flex-1 resize-none overflow-hidden rounded-xl border border-[#e5dcdc] bg-white p-[15px] text-base font-normal leading-normal text-[#181111] placeholder:text-[#886364] focus:border-[#e5dcdc] focus:outline-0 focus:ring-0"
                    required
                  />
                </label>
              </div>
              <div class="flex max-w-[480px] flex-wrap items-end gap-4 px-4 py-3">
                <label class="flex min-w-40 flex-1 flex-col">
                  <p class="pb-2 text-base font-medium leading-normal text-[#181111]">Confirm Password</p>
                  <input
                    id="confirmPassword"
                    name="confirmPassword"
                    type="password"
                    placeholder="Confirm your password"
                    class="form-input flex h-14 min-w-0 flex-1 resize-none overflow-hidden rounded-xl border border-[#e5dcdc] bg-white p-[15px] text-base font-normal leading-normal text-[#181111] placeholder:text-[#886364] focus:border-[#e5dcdc] focus:outline-0 focus:ring-0"
                    required
                  />
                </label>
              </div>
              <div class="flex items-center justify-between gap-4 bg-white px-4 min-h-14">
                <label for="terms" class="flex-1 truncate text-base font-normal leading-normal text-[#181111]">I agree to the terms and conditions</label>
                <div class="shrink-0">
                  <div class="flex size-7 items-center justify-center">
                    <input
                      id="terms"
                      name="terms"
                      type="checkbox"
                      class="h-5 w-5 rounded border-2 border-[#e5dcdc] bg-transparent text-[#e82630] checked:border-[#e82630] checked:bg-[#e82630] checked:bg-[image:--checkbox-tick-svg] focus:border-[#e5dcdc] focus:outline-none focus:ring-0"
                      required
                    />
                  </div>
                </div>
              </div>
              <div class="flex px-4 py-3">
                <button
                  id="signUpBtn"
                  type="submit"
                  class="flex h-10 min-w-[84px] max-w-[480px] flex-1 cursor-pointer items-center justify-center overflow-hidden rounded-full bg-[#e82630] px-4 text-sm font-bold leading-normal tracking-[0.015em] text-white"
                >
                  <span class="truncate">Sign Up</span>
                </button>
              </div>
              <div id="errorMsg" class="px-4 py-2 text-sm font-medium text-red-600"></div>
            </form>
            <p class="px-4 pb-3 pt-1 text-center text-sm font-normal leading-normal text-[#886364]">Already have an account?</p>
            <p id="loginText" class="cursor-pointer px-4 pb-3 pt-1 text-center text-sm font-normal leading-normal text-[#886364] underline">Log in</p>
          </div>
        </div>
      </div>
    </div>
    <script>
      document.getElementById('homeBtn').addEventListener('click', function (e) {
        e.preventDefault();
        window.location.href = 'home.php';
      });
      document.getElementById('loginBtn').addEventListener('click', function (e) {
        e.preventDefault();
        window.location.href = 'login.php';
      });

      document.getElementById('registerForm').addEventListener('submit', function (e) {
        e.preventDefault();

        const form = e.target;
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirmPassword');
        const errorMsg = document.getElementById('errorMsg');
        errorMsg.textContent = '';

        if (passwordInput.value !== confirmPasswordInput.value) {
          errorMsg.textContent = 'Passwords do not match.';
          return;
        }

        if (passwordInput.value.length < <?php echo PASSWORD_MIN_LENGTH; ?>) {
          errorMsg.textContent = 'Password must be at least <?php echo PASSWORD_MIN_LENGTH; ?> characters long.';
          return;
        }

        const formData = new FormData(form);

        fetch('register.php', {
          method: 'POST',
          body: formData,
        })
          .then((response) => {
            if (!response.ok) {
              throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
          })
          .then((data) => {
            if (data.success) {
              alert(data.message);
              window.location.href = 'login.php';
            } else {
              errorMsg.textContent = data.message;
            }
          })
          .catch((error) => {
            errorMsg.textContent = 'An error occurred. Please try again.';
            console.error('Error:', error);
          });
      });

      document.getElementById('loginText').addEventListener('click', function (e) {
        e.preventDefault();
        window.location.href = 'login.php';
      });
    </script>
</body>
</html>