<?php
require_once '../includes/functions.php';

$db = getDB();

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Search filters
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$job_type = isset($_GET['job_type']) ? sanitize($_GET['job_type']) : '';
$location = isset($_GET['location']) ? sanitize($_GET['location']) : '';

// Build query
$where_clauses = ["status = 'active'"];
$params = [];

if ($search) {
    $where_clauses[] = "(title LIKE ? OR company LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($job_type) {
    $where_clauses[] = "job_type = ?";
    $params[] = $job_type;
}

if ($location) {
    $where_clauses[] = "location LIKE ?";
    $params[] = "%$location%";
}

$where_sql = implode(" AND ", $where_clauses);

// Get total count
$count_stmt = $db->prepare("SELECT COUNT(*) as count FROM job_listings WHERE $where_sql");
$count_stmt->execute($params);
$total_jobs = $count_stmt->fetch()['count'];
$total_pages = ceil($total_jobs / $per_page);

// Get jobs
$params[] = $per_page;
$params[] = $offset;
$stmt = $db->prepare("
    SELECT * FROM job_listings 
    WHERE $where_sql 
    ORDER BY created_at DESC 
    LIMIT ? OFFSET ?
");
$stmt->execute($params);
$jobs = $stmt->fetchAll();

$page_title = 'Browse Jobs';
require_once '../includes/header.php';
?>

<div class="container" style="margin-top: 40px; margin-bottom: 60px;">
    <div class="page-header">
        <h1>Browse Jobs</h1>
        <p>Discover opportunities posted by our alumni network</p>
    </div>

    <!-- Search & Filters -->
    <div class="search-filters card" style="margin-bottom: 32px;">
        <form method="GET" action="" class="filter-form">
            <div class="form-row">
                <div class="form-group">
                    <input type="text" name="search" placeholder="Search jobs..." value="<?php echo htmlspecialchars($search); ?>">
                </div>

                <div class="form-group">
                    <select name="job_type">
                        <option value="">All Types</option>
                        <option value="full-time" <?php echo $job_type === 'full-time' ? 'selected' : ''; ?>>Full-time</option>
                        <option value="part-time" <?php echo $job_type === 'part-time' ? 'selected' : ''; ?>>Part-time</option>
                        <option value="contract" <?php echo $job_type === 'contract' ? 'selected' : ''; ?>>Contract</option>
                        <option value="remote" <?php echo $job_type === 'remote' ? 'selected' : ''; ?>>Remote</option>
                    </select>
                </div>

                <div class="form-group">
                    <input type="text" name="location" placeholder="Location..." value="<?php echo htmlspecialchars($location); ?>">
                </div>

                <button type="submit" class="btn-primary">Search</button>
            </div>
        </form>
    </div>

    <!-- Results -->
    <div class="results-header">
        <p>Found <?php echo $total_jobs; ?> jobs</p>
    </div>

    <!-- Job Listings -->
    <div class="jobs-list">
        <?php if (empty($jobs)): ?>
            <div class="card">
                <p style="text-align: center; padding: 40px;">No jobs found matching your criteria.</p>
            </div>
        <?php else: ?>
            <?php foreach ($jobs as $job): ?>
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
                    <span>🕒 <?php echo timeAgo($job['created_at']); ?></span>
                </div>

                <p class="job-description"><?php echo htmlspecialchars(substr($job['description'], 0, 200)); ?>...</p>
                
                <div class="job-footer">
                    <?php if ($job['posted_by_name']): ?>
                    <span class="posted-by">Posted by: <?php echo htmlspecialchars($job['posted_by_name']); ?></span>
                    <?php endif; ?>
                    <a href="job-detail.php?id=<?php echo $job['id']; ?>" class="btn-secondary">View Details</a>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&job_type=<?php echo urlencode($job_type); ?>&location=<?php echo urlencode($location); ?>" class="btn-secondary">Previous</a>
        <?php endif; ?>
        
        <span class="page-info">Page <?php echo $page; ?> of <?php echo $total_pages; ?></span>
        
        <?php if ($page < $total_pages): ?>
            <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&job_type=<?php echo urlencode($job_type); ?>&location=<?php echo urlencode($location); ?>" class="btn-secondary">Next</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>