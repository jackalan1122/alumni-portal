<?php
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$event_id = isset($data['event_id']) ? (int)$data['event_id'] : 0;

if (!$event_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid event ID']);
    exit;
}

$user_id = $_SESSION['user_id'];
$db = getDB();

try {
    // Check if event exists and get details
    $stmt = $db->prepare("
        SELECT e.*, 
               (SELECT COUNT(*) FROM event_registrations WHERE event_id = e.id) as registered_count
        FROM events e
        WHERE e.id = ?
    ");
    $stmt->execute([$event_id]);
    $event = $stmt->fetch();
    
    if (!$event) {
        echo json_encode(['success' => false, 'message' => 'Event not found']);
        exit;
    }
    
    // Check if event is full
    if ($event['max_attendees'] && $event['registered_count'] >= $event['max_attendees']) {
        echo json_encode(['success' => false, 'message' => 'Event is full']);
        exit;
    }
    
    // Check if already registered
    $stmt = $db->prepare("SELECT id FROM event_registrations WHERE event_id = ? AND user_id = ?");
    $stmt->execute([$event_id, $user_id]);
    
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Already registered for this event']);
        exit;
    }
    
    // Register for event
    $stmt = $db->prepare("INSERT INTO event_registrations (event_id, user_id) VALUES (?, ?)");
    $stmt->execute([$event_id, $user_id]);
    
    logActivity($user_id, 'event_registration', 'Registered for event: ' . $event['title']);
    
    echo json_encode(['success' => true, 'message' => 'Successfully registered for event']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to register for event']);
}
?>