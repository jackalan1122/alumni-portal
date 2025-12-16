<?php
require_once '../includes/functions.php';
requireLogin();

$user = getCurrentUser();
$stats = getUserStats($user['id']);
$recent_activity = getRecentActivity($user['id']);

// Get upcoming events
$db = getDB();
$stmt = $db->query("
    SELECT * FROM events 
    WHERE event_date >= CURDATE() 
    ORDER BY event_date ASC 
    LIMIT 3
");
$upcoming_events = $stmt->fetchAll();

// Get recommended jobs
$stmt = $db->query("
    SELECT * FROM job_listings 
    WHERE status = 'active' 
    ORDER BY created_at DESC 
    LIMIT 2
");
$recommended_jobs = $stmt->fetchAll();

$page_title = 'Dashboard';
$include_dashboard_css = true;
$include_dashboard_js = true;
require_once '../includes/header.php';
?>

<div class="dashboard-layout">
    <!-- Sidebar -->
    <aside class="dashboard-sidebar">
        <div class="sidebar-header">
            <div class="user-avatar">
                <?php if ($user['profile_image']): ?>
                    <img src="<?php echo SITE_URL . '/uploads/' . $user['profile_image']; ?>" alt="Profile">
                <?php else: ?>
                    <div class="avatar-placeholder">
                        <?php echo strtoupper(substr($user['first_name'], 0, 1)); ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="user-info">
                <h3><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h3>
                <p class="user-role"><?php echo ucfirst($user['user_type']); ?></p>
            </div>
        </div>

        <nav class="dashboard-nav">
            <ul class="dashboard-menu">
                <li class="active"><a href="dashboard.php">Dashboard</a></li>
                <li><a href="browse-jobs.php">Browse Jobs</a></li>
                <li><a href="my-applications.php">My Applications</a></li>
                <li><a href="saved-jobs.php">Saved Jobs</a></li>
                <li><a href="events.php">Events</a></li>
                <li><a href="profile.php">Profile</a></li>
                <?php if ($user['user_type'] === 'admin'): ?>
                <li><a href="../admin/index.php">Admin Panel</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="dashboard-main">
        <div class="dashboard-header">
            <h1>Welcome back, <?php echo htmlspecialchars($user['first_name']); ?>!</h1>
            <p>Here's what's happening with your job search</p>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid grid grid-cols-4">
            <div class="stat-card card">
                <div class="stat-icon" style="background: #DBEAFE; color: #3B82F6;">
                    📤
                </div>
                <p class="stat-label">Applications</p>
                <p class="stat-value"><?php echo $stats['applications']; ?></p>
                <p class="stat-change positive">Active</p>
            </div>

            <div class="stat-card card">
                <div class="stat-icon" style="background: #E9D5FF; color: #9333EA;">
                    🔖
                </div>
                <p class="stat-label">Saved Jobs</p>
                <p class="stat-value"><?php echo $stats['saved_jobs']; ?></p>
                <p class="stat-change">Review later</p>
            </div>

            <div class="stat-card card">
                <div class="stat-icon" style="background: #D1FAE5; color: #10B981;">
                    👥
                </div>
                <p class="stat-label">Profile Views</p>
                <p class="stat-value"><?php echo $stats['profile_views']; ?></p>
                <p class="stat-change positive">This month</p>
            </div>

            <div class="stat-card card">
                <div class="stat-icon" style="background: #FED7AA; color: #F97316;">
                    📈
                </div>
                <p class="stat-label">Response Rate</p>
                <p class="stat-value">68%</p>
                <p class="stat-change">Above average</p>
            </div>
        </div>

        <!-- Recent Activity & Upcoming Events -->
        <div class="dashboard-content grid" style="grid-template-columns: 2fr 1fr; gap: 24px; margin-top: 32px;">
            <div class="activity-section card">
                <h2>Recent Activity</h2>
                <div class="activity-list">
                    <?php if (empty($recent_activity)): ?>
                        <p class="no-data">No recent activity</p>
                    <?php else: ?>
                        <?php foreach ($recent_activity as $activity): ?>
                        <div class="activity-item">
                            <div class="activity-dot <?php echo $activity['activity_type']; ?>"></div>
                            <div class="activity-content">
                                <p><?php echo htmlspecialchars($activity['activity_description']); ?></p>
                                <span class="activity-time"><?php echo timeAgo($activity['created_at']); ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="events-section card">
                <h2>Upcoming Events</h2>
                <?php if (empty($upcoming_events)): ?>
                    <p class="no-data">No upcoming events</p>
                <?php else: ?>
                    <?php foreach ($upcoming_events as $event): ?>
                    <div class="event-item">
                        <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                        <p class="event-date">📅 <?php echo formatDate($event['event_date']); ?></p>
                        <p class="event-location">📍 <?php echo htmlspecialchars($event['location']); ?></p>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recommended Jobs -->
        <div class="recommended-jobs card" style="margin-top: 32px;">
            <h2>Recommended for You</h2>
            <div class="job-grid grid grid-cols-2">
                <?php foreach ($recommended_jobs as $job): ?>
                <div class="job-card-mini">
                    <h3><?php echo htmlspecialchars($job['title']); ?></h3>
                    <p class="job-company"><?php echo htmlspecialchars($job['company']); ?></p>
                    <div class="job-meta">
                        <span>📍 <?php echo htmlspecialchars($job['location']); ?></span>
                        <?php if ($job['salary_range']): ?>
                        <span>💰 <?php echo htmlspecialchars($job['salary_range']); ?></span>
                        <?php endif; ?>
                    </div>
                    <span class="job-type-badge"><?php echo htmlspecialchars($job['job_type']); ?></span>
                    <a href="job-detail.php?id=<?php echo $job['id']; ?>" class="job-link">View Details →</a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>
</div>

<?php require_once '../includes/footer.php'; ?>