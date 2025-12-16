<?php
require_once '../includes/functions.php';

if (isLoggedIn()) {
    header('Location: ' . SITE_URL . '/pages/dashboard.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = sanitize($_POST['first_name']);
    $last_name = sanitize($_POST['last_name']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $user_type = sanitize($_POST['user_type']);
    $graduation_year = sanitize($_POST['graduation_year']);
    $major = sanitize($_POST['major']);
    
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
        $error = 'All fields are required';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format';
    } else {
        $db = getDB();
        
        // Check if email exists
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            $error = 'Email already registered';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $db->prepare("
                INSERT INTO users (first_name, last_name, email, password, user_type, graduation_year, major) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            if ($stmt->execute([$first_name, $last_name, $email, $hashed_password, $user_type, $graduation_year, $major])) {
                $success = 'Registration successful! Please login.';
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}

$page_title = 'Register';
require_once '../includes/header.php';
?>

<div class="auth-container">
    <div class="auth-card">
        <h2>Create Account</h2>
        <p class="auth-subtitle">Join our alumni network</p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <form method="POST" action="" class="auth-form">
            <div class="form-row">
                <div class="form-group">
                    <label>First Name</label>
                    <input type="text" name="first_name" required value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label>Last Name</label>
                    <input type="text" name="last_name" required value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>User Type</label>
                    <select name="user_type" required>
                        <option value="student">Student</option>
                        <option value="alumni">Alumni</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Graduation Year</label>
                    <input type="text" name="graduation_year" placeholder="e.g., 2020" value="<?php echo isset($_POST['graduation_year']) ? htmlspecialchars($_POST['graduation_year']) : ''; ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Major</label>
                <input type="text" name="major" placeholder="e.g., Computer Science" value="<?php echo isset($_POST['major']) ? htmlspecialchars($_POST['major']) : ''; ?>">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>

                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" required>
                </div>
            </div>

            <button type="submit" class="btn-primary btn-full">Create Account</button>
        </form>

        <p class="auth-footer">
            Already have an account? <a href="login.php">Login here</a>
        </p>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>