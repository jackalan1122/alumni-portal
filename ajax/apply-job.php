<?php
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$job_id = isset($data['job_id']) ? (int)$data['job_id'] : 0;

if (!$job_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid job ID']);
    exit;
}

$user_id = $_SESSION['user_id'];
$db = getDB();

// Check if already applied
if (hasApplied($user_id, $job_id)) {
    echo json_encode(['success' => false, 'message' => 'Already applied to this job']);
    exit;
}

try {
    $stmt = $db->prepare("INSERT INTO job_applications (job_id, user_id, status) VALUES (?, ?, 'pending')");
    $stmt->execute([$job_id, $user_id]);
    
    // Get job title for activity log
    $stmt = $db->prepare("SELECT title FROM job_listings WHERE id = ?");
    $stmt->execute([$job_id]);
    $job = $stmt->fetch();
    
    logActivity($user_id, 'application', 'Applied to ' . $job['title']);
    
    echo json_encode(['success' => true, 'message' => 'Application submitted successfully']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to submit application']);
}
?>