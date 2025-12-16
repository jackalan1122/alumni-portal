<?php
require_once '../includes/functions.php';
requireLogin();

$user = getCurrentUser();
$db = getDB();

// Get saved jobs
$stmt = $db->prepare("
    SELECT 
        jl.*,
        sj.saved_at
    FROM saved_jobs sj
    INNER JOIN job_listings jl ON sj.job_id = jl.id
    WHERE sj.user_id = ?
    ORDER BY sj.saved_at DESC
");
$stmt->execute([$user['id']]);
$saved_jobs = $stmt->fetchAll();

$page_title = 'Saved Jobs';
$include_dashboard_css = true;
require_once '../includes/header.php';
?>

<div class="container" style="margin-top: 40px; margin-bottom: 60px;">
    <div class="page-header">
        <h1>Saved Jobs</h1>
        <p>Jobs you've bookmarked for later</p>
    </div>

    <?php if (empty($saved_jobs)): ?>
        <div class="card">
            <div class="no-data">
                <div style="font-size: 48px; margin-bottom: 16px;">🔖</div>
                <h3>No Saved Jobs</h3>
                <p>Start saving jobs to review them later!</p>
                <a href="browse-jobs.php" class="btn-primary" style="margin-top: 16px;">Browse Jobs</a>
            </div>
        </div>
    <?php else: ?>
        <div class="jobs-list">
            <?php foreach ($saved_jobs as $job): ?>
            <div class="job-card-full card">
                <div class="job-header">
                    <div>
                        <h3><?php echo htmlspecialchars($job['title']); ?></h3>
                        <p class="job-company"><?php echo htmlspecialchars($job['company']); ?></p>
                    </div>
                    <span class="job-type-badge"><?php echo htmlspecialchars($job['job_type']); ?></span>
                </div>
                
                <div class="job-meta">
                    <span>📍 <?php echo htmlspecialchars($job['location']); ?></span>
                    <?php if ($job['salary_range']): ?>
                    <span>💰 <?php echo htmlspecialchars($job['salary_range']); ?></span>
                    <?php endif; ?>
                    <span>🔖 Saved <?php echo timeAgo($job['saved_at']); ?></span>
                </div>

                <p class="job-description"><?php echo htmlspecialchars(substr($job['description'], 0, 200)); ?>...</p>
                
                <div class="job-footer">
                    <a href="job-detail.php?id=<?php echo $job['id']; ?>" class="btn-primary">View Details</a>
                    <button onclick="unsaveJob(<?php echo $job['id']; ?>)" class="btn-danger">Remove</button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.btn-danger {
    background: #EF4444;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-danger:hover {
    background: #DC2626;
}
</style>

<script>
function unsaveJob(jobId) {
    if (confirm('Remove this job from your saved list?')) {
        fetch('<?php echo SITE_URL; ?>/ajax/unsave-job.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ job_id: jobId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Failed to remove job');
            }
        });
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>