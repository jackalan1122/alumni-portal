<?php
require_once '../includes/functions.php';

$job_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$job_id) {
    header('Location: browse-jobs.php');
    exit;
}

$db = getDB();
$stmt = $db->prepare("SELECT * FROM job_listings WHERE id = ?");
$stmt->execute([$job_id]);
$job = $stmt->fetch();

if (!$job) {
    header('Location: browse-jobs.php');
    exit;
}

// Update view count
$stmt = $db->prepare("UPDATE job_listings SET views = views + 1 WHERE id = ?");
$stmt->execute([$job_id]);

// Check if user has saved/applied
$is_saved = false;
$has_applied = false;
if (isLoggedIn()) {
    $user_id = $_SESSION['user_id'];
    $is_saved = isJobSaved($user_id, $job_id);
    $has_applied = hasApplied($user_id, $job_id);
}

$page_title = $job['title'];
require_once '../includes/header.php';
?>

<div class="container" style="margin-top: 40px; margin-bottom: 60px;">
    <a href="browse-jobs.php" class="back-link">← Back to Jobs</a>

    <div class="job-detail-container">
        <div class="job-detail-main card">
            <div class="job-detail-header">
                <div>
                    <h1><?php echo htmlspecialchars($job['title']); ?></h1>
                    <p class="job-company-large"><?php echo htmlspecialchars($job['company']); ?></p>
                    <div class="job-meta-large">
                        <span>📍 <?php echo htmlspecialchars($job['location']); ?></span>
                        <span>💼 <?php echo htmlspecialchars($job['job_type']); ?></span>
                        <?php if ($job['salary_range']): ?>
                        <span>💰 <?php echo htmlspecialchars($job['salary_range']); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="job-actions">
                <?php if (isLoggedIn()): ?>
                    <?php if (!$has_applied): ?>
                        <button onclick="applyJob(<?php echo $job_id; ?>)" class="btn-primary">Apply Now</button>
                    <?php else: ?>
                        <button class="btn-secondary" disabled>Already Applied</button>
                    <?php endif; ?>
                    
                    <button onclick="saveJob(<?php echo $job_id; ?>)" class="btn-secondary" id="saveBtn">
                        <?php echo $is_saved ? '✓ Saved' : '🔖 Save Job'; ?>
                    </button>
                <?php else: ?>
                    <a href="<?php echo SITE_URL; ?>/auth/login.php" class="btn-primary">Login to Apply</a>
                <?php endif; ?>
            </div>

            <div class="job-section">
                <h2>Job Description</h2>
                <p><?php echo nl2br(htmlspecialchars($job['description'])); ?></p>
            </div>

            <?php if ($job['requirements']): ?>
            <div class="job-section">
                <h2>Requirements</h2>
                <div class="requirements-list">
                    <?php 
                    $requirements = explode("\n", $job['requirements']);
                    foreach ($requirements as $req): 
                        if (trim($req)):
                    ?>
                    <div class="requirement-item">
                        <span class="bullet">•</span>
                        <?php echo htmlspecialchars(trim($req)); ?>
                    </div>
                    <?php 
                        endif;
                    endforeach; 
                    ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($job['benefits']): ?>
            <div class="job-section">
                <h2>Benefits</h2>
                <div class="benefits-list">
                    <?php 
                    $benefits = explode("\n", $job['benefits']);
                    foreach ($benefits as $benefit): 
                        if (trim($benefit)):
                    ?>
                    <div class="benefit-item">
                        <span class="check">✓</span>
                        <?php echo htmlspecialchars(trim($benefit)); ?>
                    </div>
                    <?php 
                        endif;
                    endforeach; 
                    ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($job['posted_by_name']): ?>
            <div class="posted-by-section">
                <p><strong>Posted by:</strong> <?php echo htmlspecialchars($job['posted_by_name']); ?></p>
                <p class="text-muted">Connect with fellow alumni and get insider insights</p>
            </div>
            <?php endif; ?>
        </div>

        <div class="job-detail-sidebar">
            <div class="card">
                <h3>Job Information</h3>
                <div class="info-list">
                    <div class="info-item">
                        <span class="info-label">Posted</span>
                        <span class="info-value"><?php echo timeAgo($job['created_at']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Views</span>
                        <span class="info-value"><?php echo number_format($job['views']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Status</span>
                        <span class="info-value"><?php echo ucfirst($job['status']); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function saveJob(jobId) {
    fetch('<?php echo SITE_URL; ?>/ajax/save-job.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ job_id: jobId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('saveBtn').innerHTML = '✓ Saved';
            alert('Job saved successfully!');
        } else {
            alert(data.message || 'Failed to save job');
        }
    });
}

function applyJob(jobId) {
    if (confirm('Are you sure you want to apply for this job?')) {
        fetch('<?php echo SITE_URL; ?>/ajax/apply-job.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ job_id: jobId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Application submitted successfully!');
                location.reload();
            } else {
                alert(data.message || 'Failed to submit application');
            }
        });
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>