<?php
$page_title = 'Home';
require_once 'includes/header.php';

// Get featured jobs
$db = getDB();
$stmt = $db->query("SELECT * FROM job_listings WHERE status = 'active' ORDER BY created_at DESC LIMIT 4");
$featured_jobs = $stmt->fetchAll();

// Get job count
$stmt = $db->query("SELECT COUNT(*) as count FROM job_listings WHERE status = 'active'");
$job_count = $stmt->fetch()['count'];

// Get alumni count
$stmt = $db->query("SELECT COUNT(*) as count FROM users WHERE user_type = 'alumni'");
$alumni_count = $stmt->fetch()['count'];
?>

<div class="hero-section">
    <div class="container">
        <h1>Launch Your Next Career Move</h1>
        <p>Exclusive opportunities from our alumni network</p>
        <div class="hero-stats">
            <div class="stat-box">
                <span class="number"><?php echo number_format($alumni_count); ?>+</span>
                <span class="label">Active Alumni</span>
            </div>
            <div class="stat-box">
                <span class="number"><?php echo number_format($job_count); ?>+</span>
                <span class="label">Job Postings</span>
            </div>
            <div class="stat-box">
                <span class="number">89%</span>
                <span class="label">Placement Rate</span>
            </div>
        </div>
    </div>
</div>

<div class="container" style="margin-top: 60px; margin-bottom: 60px;">
    <div class="grid grid-cols-3" style="margin-bottom: 60px;">
        <div class="card">
            <div class="feature-icon">💼</div>
            <h3>Career Counseling</h3>
            <p>One-on-one guidance from industry professionals</p>
        </div>
        <div class="card">
            <div class="feature-icon">📝</div>
            <h3>Resume Review</h3>
            <p>Get expert feedback on your resume and cover letter</p>
        </div>
        <div class="card">
            <div class="feature-icon">📈</div>
            <h3>Skill Development</h3>
            <p>Access workshops and courses to enhance your skills</p>
        </div>
    </div>

    <h2 style="margin-bottom: 24px;">Featured Opportunities</h2>
    <div class="grid grid-cols-2">
        <?php foreach ($featured_jobs as $job): ?>
        <div class="job-card">
            <h3><?php echo htmlspecialchars($job['title']); ?></h3>
            <p class="job-company"><?php echo htmlspecialchars($job['company']); ?></p>
            <div class="job-meta">
                <span>📍 <?php echo htmlspecialchars($job['location']); ?></span>
                <?php if ($job['salary_range']): ?>
                <span>💰 <?php echo htmlspecialchars($job['salary_range']); ?></span>
                <?php endif; ?>
            </div>
            <span class="job-type-badge"><?php echo htmlspecialchars($job['job_type']); ?></span>
            <a href="<?php echo SITE_URL; ?>/pages/job-detail.php?id=<?php echo $job['id']; ?>" class="job-link">View Details →</a>
        </div>
        <?php endforeach; ?>
    </div>

    <div style="text-align: center; margin-top: 40px;">
        <a href="<?php echo SITE_URL; ?>/pages/browse-jobs.php" class="btn-primary">Browse All Jobs</a>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>