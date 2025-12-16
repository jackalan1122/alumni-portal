<?php
// Start Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Get current user data
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . SITE_URL . '/auth/login.php');
        exit;
    }
}

// Redirect if not admin
function requireAdmin() {
    requireLogin();
    $user = getCurrentUser();
    if ($user['user_type'] !== 'admin') {
        header('Location: ' . SITE_URL . '/index.php');
        exit;
    }
}

// Sanitize input
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Format date
function formatDate($date) {
    return date('M d, Y', strtotime($date));
}

// Time ago function
function timeAgo($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;
    
    if ($diff < 60) return $diff . ' seconds ago';
    if ($diff < 3600) return floor($diff / 60) . ' minutes ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    if ($diff < 2592000) return floor($diff / 604800) . ' weeks ago';
    return formatDate($datetime);
}

// Get user statistics
function getUserStats($user_id) {
    $db = getDB();
    
    // Applications count
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM job_applications WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $applications = $stmt->fetch()['count'];
    
    // Saved jobs count
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM saved_jobs WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $saved = $stmt->fetch()['count'];
    
    // Profile views count
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM profile_views WHERE profile_user_id = ?");
    $stmt->execute([$user_id]);
    $views = $stmt->fetch()['count'];
    
    return [
        'applications' => $applications,
        'saved_jobs' => $saved,
        'profile_views' => $views
    ];
}

// Get recent activity
function getRecentActivity($user_id, $limit = 5) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT * FROM activity_log 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT ?
    ");
    $stmt->execute([$user_id, $limit]);
    return $stmt->fetchAll();
}

// Log activity
function logActivity($user_id, $type, $description) {
    $db = getDB();
    $stmt = $db->prepare("
        INSERT INTO activity_log (user_id, activity_type, activity_description) 
        VALUES (?, ?, ?)
    ");
    return $stmt->execute([$user_id, $type, $description]);
}

// Check if job is saved
function isJobSaved($user_id, $job_id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT id FROM saved_jobs WHERE user_id = ? AND job_id = ?");
    $stmt->execute([$user_id, $job_id]);
    return $stmt->fetch() !== false;
}

// Check if already applied
function hasApplied($user_id, $job_id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT id FROM job_applications WHERE user_id = ? AND job_id = ?");
    $stmt->execute([$user_id, $job_id]);
    return $stmt->fetch() !== false;
}

// Upload file
function uploadFile($file, $allowed_types = ['jpg', 'jpeg', 'png', 'pdf']) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Upload error'];
    }
    
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'message' => 'File too large'];
    }
    
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $allowed_types)) {
        return ['success' => false, 'message' => 'Invalid file type'];
    }
    
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $filepath = UPLOAD_PATH . $filename;
    
    if (!is_dir(UPLOAD_PATH)) {
        mkdir(UPLOAD_PATH, 0777, true);
    }
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => true, 'filename' => $filename];
    }
    
    return ['success' => false, 'message' => 'Failed to move file'];
}

// Generate CSRF token
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF token
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>