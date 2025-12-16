<?php
require_once '../includes/functions.php';
requireLogin();

$user = getCurrentUser();
$db = getDB();

// Get user's applications with job details
$stmt = $db->prepare("
    SELECT 
        ja.*,
        jl.title,
        jl.company,
        jl.location,
        jl.job_type,
        jl.salary_range
    FROM job_applications ja
    INNER JOIN job_listings jl ON ja.job_id = jl.id
    WHERE ja.user_id = ?
    ORDER BY ja.applied_at DESC
");
$stmt->execute([$user['id']]);
$applications = $stmt->fetchAll();

$page_title = 'My Applications';
$include_dashboard_css = true;
require_once '../includes/header.php';
?>

<div class="container" style="margin-top: 40px; margin-bottom: 60px;">
    <div class="page-header">
        <h1>My Applications</h1>
        <p>Track your job applications and their status</p>
    </div>

    <?php if (empty($applications)): ?>
        <div class="card">
            <div class="no-data">
                <div style="font-size: 48px; margin-bottom: 16px;">📝</div>
                <h3>No Applications Yet</h3>
                <p>You haven't applied to any jobs yet. Start browsing!</p>
                <a href="browse-jobs.php" class="btn-primary" style="margin-top: 16px;">Browse Jobs</a>
            </div>
        </div>
    <?php else: ?>
        <div class="applications-grid">
            <?php foreach ($applications as $app): ?>
            <div class="application-card card">
                <div class="application-header">
                    <div>
                        <h3><?php echo htmlspecialchars($app['title']); ?></h3>
                        <p class="job-company"><?php echo htmlspecialchars($app['company']); ?></p>
                        <div class="job-meta">
                            <span>📍 <?php echo htmlspecialchars($app['location']); ?></span>
                            <span>💼 <?php echo htmlspecialchars($app['job_type']); ?></span>
                        </div>
                    </div>
                    <span class="status-badge status-<?php echo $app['status']; ?>">
                        <?php echo ucfirst($app['status']); ?>
                    </span>
                </div>

                <div class="application-details">
                    <div class="detail-item">
                        <span class="detail-label">Applied On:</span>
                        <span class="detail-value"><?php echo formatDate($app['applied_at']); ?></span>
                    </div>
                    
                    <?php if ($app['cover_letter']): ?>
                    <div class="detail-item">
                        <span class="detail-label">Cover Letter:</span>
                        <span class="detail-value">Submitted</span>
                    </div>
                    <?php endif; ?>

                    <?php if ($app['resume_path']): ?>
                    <div class="detail-item">
                        <span class="detail-label">Resume:</span>
                        <a href="<?php echo SITE_URL . '/uploads/' . $app['resume_path']; ?>" target="_blank" class="detail-value link">View Resume →</a>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="application-actions">
                    <a href="job-detail.php?id=<?php echo $app['job_id']; ?>" class="btn-secondary">View Job</a>
                    
                    <?php if ($app['status'] === 'pending'): ?>
                    <button onclick="withdrawApplication(<?php echo $app['id']; ?>)" class="btn-danger">Withdraw</button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Application Statistics -->
        <div class="application-stats grid grid-cols-4" style="margin-top: 40px;">
            <?php
            $total = count($applications);
            $pending = count(array_filter($applications, fn($a) => $a['status'] === 'pending'));
            $reviewed = count(array_filter($applications, fn($a) => $a['status'] === 'reviewed'));
            $accepted = count(array_filter($applications, fn($a) => $a['status'] === 'accepted'));
            $rejected = count(array_filter($applications, fn($a) => $a['status'] === 'rejected'));
            ?>
            
            <div class="card text-center">
                <h3 style="font-size: 32px; color: var(--primary-blue);"><?php echo $total; ?></h3>
                <p>Total Applications</p>
            </div>
            
            <div class="card text-center">
                <h3 style="font-size: 32px; color: #F59E0B;"><?php echo $pending; ?></h3>
                <p>Pending Review</p>
            </div>
            
            <div class="card text-center">
                <h3 style="font-size: 32px; color: #10B981;"><?php echo $accepted; ?></h3>
                <p>Accepted</p>
            </div>
            
            <div class="card text-center">
                <h3 style="font-size: 32px; color: var(--primary-purple);"><?php echo $reviewed; ?></h3>
                <p>Under Review</p>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.applications-grid {
    display: grid;
    gap: 24px;
}

.application-card {
    transition: transform 0.3s ease;
}

.application-card:hover {
    transform: translateY(-4px);
}

.application-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 20px;
    padding-bottom: 20px;
    border-bottom: 1px solid var(--gray-200);
}

.status-badge {
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 600;
}

.status-pending {
    background: #FEF3C7;
    color: #92400E;
}

.status-reviewed {
    background: #DBEAFE;
    color: #1E40AF;
}

.status-accepted {
    background: #D1FAE5;
    color: #065F46;
}

.status-rejected {
    background: #FEE2E2;
    color: #991B1B;
}

.application-details {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-bottom: 20px;
}

.detail-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.detail-label {
    font-weight: 600;
    color: var(--gray-700);
}

.detail-value {
    color: var(--gray-600);
}

.detail-value.link {
    color: var(--primary-blue);
    text-decoration: none;
}

.detail-value.link:hover {
    text-decoration: underline;
}

.application-actions {
    display: flex;
    gap: 12px;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid var(--gray-200);
}

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

.text-center {
    text-align: center;
}
</style>

<script>
function withdrawApplication(applicationId) {
    if (confirm('Are you sure you want to withdraw this application?')) {
        fetch('<?php echo SITE_URL; ?>/ajax/withdraw-application.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ application_id: applicationId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Application withdrawn successfully');
                location.reload();
            } else {
                alert(data.message || 'Failed to withdraw application');
            }
        })
        .catch(error => {
            alert('An error occurred');
        });
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>