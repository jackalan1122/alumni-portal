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

// Check if already saved
if (isJobSaved($user_id, $job_id)) {
    echo json_encode(['success' => false, 'message' => 'Job already saved']);
    exit;
}

try {
    $stmt = $db->prepare("INSERT INTO saved_jobs (user_id, job_id) VALUES (?, ?)");
    $stmt->execute([$user_id, $job_id]);
    
    logActivity($user_id, 'job_post', 'Saved a job');
    
    echo json_encode(['success' => true, 'message' => 'Job saved successfully']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to save job']);
}
?>