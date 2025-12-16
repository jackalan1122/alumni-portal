<?php
require_once __DIR__ . '/functions.php';
$current_user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?><?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <?php if (isset($include_dashboard_css) && $include_dashboard_css): ?>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/dashboard.css">
    <?php endif; ?>
</head>
<body>
    <header class="site-header">
        <div class="container">
            <div class="header-inner">
                <div class="site-branding">
                    <a href="<?php echo SITE_URL; ?>/index.php">
                        <h1><?php echo SITE_NAME; ?></h1>
                    </a>
                </div>

                <nav class="main-navigation">
                    <ul>
                        <li><a href="<?php echo SITE_URL; ?>/index.php">Home</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/pages/browse-jobs.php">Browse Jobs</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/pages/events.php">Events</a></li>
                        <?php if (isLoggedIn()): ?>
                        <li><a href="<?php echo SITE_URL; ?>/pages/dashboard.php">Dashboard</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>

                <div class="header-actions">
                    <?php if (isLoggedIn()): ?>
                        <a href="<?php echo SITE_URL; ?>/pages/profile.php" class="btn-secondary">
                            <?php echo htmlspecialchars($current_user['first_name']); ?>
                        </a>
                        <a href="<?php echo SITE_URL; ?>/auth/logout.php" class="btn-primary">Logout</a>
                    <?php else: ?>
                        <a href="<?php echo SITE_URL; ?>/auth/login.php" class="btn-secondary">Login</a>
                        <a href="<?php echo SITE_URL; ?>/auth/register.php" class="btn-primary">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>