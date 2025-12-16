<?php
require_once '../includes/functions.php';

$db = getDB();

// Get filter parameters
$event_type = isset($_GET['type']) ? sanitize($_GET['type']) : '';
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

// Build query
$where_clauses = ["event_date >= CURDATE()"];
$params = [];

if ($event_type) {
    $where_clauses[] = "event_type = ?";
    $params[] = $event_type;
}

if ($search) {
    $where_clauses[] = "(title LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_sql = implode(" AND ", $where_clauses);

// Get upcoming events
$stmt = $db->prepare("
    SELECT e.*, 
           (SELECT COUNT(*) FROM event_registrations WHERE event_id = e.id) as registered_count
    FROM events e
    WHERE $where_sql
    ORDER BY event_date ASC, event_time ASC
");
$stmt->execute($params);
$events = $stmt->fetchAll();

// Get past events count
$stmt = $db->query("SELECT COUNT(*) as count FROM events WHERE event_date < CURDATE()");
$past_events_count = $stmt->fetch()['count'];

// Check user registrations if logged in
$user_registrations = [];
if (isLoggedIn()) {
    $user_id = $_SESSION['user_id'];
    $stmt = $db->prepare("SELECT event_id FROM event_registrations WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user_registrations = array_column($stmt->fetchAll(), 'event_id');
}

$page_title = 'Events';
require_once '../includes/header.php';
?>

<div class="container" style="margin-top: 40px; margin-bottom: 60px;">
    <div class="page-header">
        <h1>Upcoming Events</h1>
        <p>Network, learn, and grow with fellow alumni</p>
    </div>

    <!-- Event Stats -->
    <div class="event-stats grid grid-cols-3" style="margin-bottom: 32px;">
        <div class="card text-center">
            <h3 style="font-size: 32px; color: var(--primary-blue);"><?php echo count($events); ?></h3>
            <p>Upcoming Events</p>
        </div>
        
        <div class="card text-center">
            <h3 style="font-size: 32px; color: #10B981;"><?php echo count($user_registrations); ?></h3>
            <p>Your Registrations</p>
        </div>
        
        <div class="card text-center">
            <h3 style="font-size: 32px; color: var(--primary-purple);"><?php echo $past_events_count; ?></h3>
            <p>Past Events</p>
        </div>
    </div>

    <!-- Search & Filters -->
    <div class="search-filters card" style="margin-bottom: 32px;">
        <form method="GET" action="" class="filter-form">
            <div class="form-row">
                <div class="form-group">
                    <input type="text" name="search" placeholder="Search events..." value="<?php echo htmlspecialchars($search); ?>">
                </div>

                <div class="form-group">
                    <select name="type">
                        <option value="">All Types</option>
                        <option value="virtual" <?php echo $event_type === 'virtual' ? 'selected' : ''; ?>>Virtual</option>
                        <option value="physical" <?php echo $event_type === 'physical' ? 'selected' : ''; ?>>Physical</option>
                        <option value="hybrid" <?php echo $event_type === 'hybrid' ? 'selected' : ''; ?>>Hybrid</option>
                    </select>
                </div>

                <button type="submit" class="btn-primary">Search</button>
            </div>
        </form>
    </div>

    <!-- Events Grid -->
    <?php if (empty($events)): ?>
        <div class="card">
            <div class="no-data">
                <div style="font-size: 48px; margin-bottom: 16px;">📅</div>
                <h3>No Upcoming Events</h3>
                <p>Check back later for new events!</p>
            </div>
        </div>
    <?php else: ?>
        <div class="events-grid grid grid-cols-3">
            <?php foreach ($events as $event): ?>
            <div class="event-card card">
                <div class="event-card-header">
                    <span class="event-type-badge event-type-<?php echo $event['event_type']; ?>">
                        <?php echo ucfirst($event['event_type']); ?>
                    </span>
                    <div class="event-date-badge">
                        <div class="date-month"><?php echo date('M', strtotime($event['event_date'])); ?></div>
                        <div class="date-day"><?php echo date('d', strtotime($event['event_date'])); ?></div>
                    </div>
                </div>

                <h3 class="event-title"><?php echo htmlspecialchars($event['title']); ?></h3>
                
                <p class="event-description">
                    <?php echo htmlspecialchars(substr($event['description'], 0, 120)); ?>...
                </p>

                <div class="event-details">
                    <div class="event-detail-item">
                        <span>📅</span>
                        <span><?php echo formatDate($event['event_date']); ?></span>
                    </div>
                    
                    <?php if ($event['event_time']): ?>
                    <div class="event-detail-item">
                        <span>🕒</span>
                        <span><?php echo date('g:i A', strtotime($event['event_time'])); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="event-detail-item">
                        <span>📍</span>
                        <span><?php echo htmlspecialchars($event['location']); ?></span>
                    </div>

                    <?php if ($event['max_attendees']): ?>
                    <div class="event-detail-item">
                        <span>👥</span>
                        <span><?php echo $event['registered_count']; ?> / <?php echo $event['max_attendees']; ?> registered</span>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="event-actions">
                    <?php if (isLoggedIn()): ?>
                        <?php if (in_array($event['id'], $user_registrations)): ?>
                            <button class="btn-secondary" disabled>✓ Registered</button>
                        <?php else: ?>
                            <?php 
                            $is_full = $event['max_attendees'] && $event['registered_count'] >= $event['max_attendees'];
                            ?>
                            <button 
                                onclick="registerEvent(<?php echo $event['id']; ?>)" 
                                class="btn-primary"
                                <?php echo $is_full ? 'disabled' : ''; ?>
                            >
                                <?php echo $is_full ? 'Event Full' : 'Register Now'; ?>
                            </button>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="<?php echo SITE_URL; ?>/auth/login.php" class="btn-primary">Login to Register</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.events-grid {
    gap: 24px;
}

.event-card {
    display: flex;
    flex-direction: column;
    transition: transform 0.3s ease;
}

.event-card:hover {
    transform: translateY(-8px);
}

.event-card-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 16px;
}

.event-type-badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.event-type-virtual {
    background: #DBEAFE;
    color: #1E40AF;
}

.event-type-physical {
    background: #D1FAE5;
    color: #065F46;
}

.event-type-hybrid {
    background: #E9D5FF;
    color: #7C3AED;
}

.event-date-badge {
    text-align: center;
    background: var(--gradient-primary);
    color: white;
    padding: 8px 12px;
    border-radius: 8px;
    min-width: 60px;
}

.date-month {
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.date-day {
    font-size: 24px;
    font-weight: 700;
    line-height: 1;
}

.event-title {
    font-size: 20px;
    margin-bottom: 12px;
    color: var(--gray-900);
}

.event-description {
    color: var(--gray-600);
    font-size: 14px;
    margin-bottom: 16px;
    flex-grow: 1;
}

.event-details {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-bottom: 16px;
    padding-top: 16px;
    border-top: 1px solid var(--gray-200);
}

.event-detail-item {
    display: flex;
    gap: 8px;
    font-size: 14px;
    color: var(--gray-600);
}

.event-actions {
    margin-top: auto;
}

.event-actions button,
.event-actions a {
    width: 100%;
    text-align: center;
}

.text-center {
    text-align: center;
}

@media (max-width: 968px) {
    .events-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
function registerEvent(eventId) {
    if (confirm('Would you like to register for this event?')) {
        fetch('<?php echo SITE_URL; ?>/ajax/register-event.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ event_id: eventId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Successfully registered for the event!');
                location.reload();
            } else {
                alert(data.message || 'Failed to register for event');
            }
        })
        .catch(error => {
            alert('An error occurred');
        });
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>