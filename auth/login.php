<?php
require_once '../includes/functions.php';

if (isLoggedIn()) {
    header('Location: ' . SITE_URL . '/pages/dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'All fields are required';
    } else {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_type'] = $user['user_type'];
            
            if ($user['user_type'] === 'admin') {
                header('Location: ' . SITE_URL . '/admin/index.php');
            } else {
                header('Location: ' . SITE_URL . '/pages/dashboard.php');
            }
            exit;
        } else {
            $error = 'Invalid email or password';
        }
    }
}

$page_title = 'Login';
require_once '../includes/header.php';
?>

<div class="auth-container">
    <div class="auth-card">
        <h2>Welcome Back</h2>
        <p class="auth-subtitle">Login to your account</p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="" class="auth-form">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>

            <button type="submit" class="btn-primary btn-full">Sign In</button>
        </form>

        <p class="auth-footer">
            Don't have an account? <a href="register.php">Register here</a>
        </p>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>