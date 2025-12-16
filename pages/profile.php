<?php
require_once '../includes/functions.php';
requireLogin();

$user = getCurrentUser();
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = sanitize($_POST['first_name']);
    $last_name = sanitize($_POST['last_name']);
    $graduation_year = sanitize($_POST['graduation_year']);
    $major = sanitize($_POST['major']);
    $current_position = sanitize($_POST['current_position']);
    $company = sanitize($_POST['company']);
    $bio = sanitize($_POST['bio']);
    
    $db = getDB();
    $stmt = $db->prepare("
        UPDATE users 
        SET first_name = ?, last_name = ?, graduation_year = ?, major = ?, 
            current_position = ?, company = ?, bio = ?
        WHERE id = ?
    ");
    
    if ($stmt->execute([$first_name, $last_name, $graduation_year, $major, $current_position, $company, $bio, $user['id']])) {
        $success = 'Profile updated successfully!';
        $user = getCurrentUser(); // Refresh user data
    } else {
        $error = 'Failed to update profile';
    }
}

$page_title = 'My Profile';
$include_dashboard_css = true;
require_once '../includes/header.php';
?>

<div class="container" style="margin-top: 40px; margin-bottom: 60px;">
    <div class="page-header">
        <h1>My Profile</h1>
        <p>Manage your personal information</p>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="profile-container">
        <div class="card">
            <div class="profile-header">
                <div class="profile-avatar-large">
                    <?php if ($user['profile_image']): ?>
                        <img src="<?php echo SITE_URL . '/uploads/' . $user['profile_image']; ?>" alt="Profile">
                    <?php else: ?>
                        <div class="avatar-placeholder-large">
                            <?php echo strtoupper(substr($user['first_name'], 0, 1)); ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div>
                    <h2><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h2>
                    <p class="profile-subtitle"><?php echo htmlspecialchars($user['email']); ?></p>
                    <p class="profile-meta">
                        <?php echo htmlspecialchars($user['major']); ?> • Class of <?php echo htmlspecialchars($user['graduation_year']); ?>
                    </p>
                </div>
            </div>

            <form method="POST" action="" class="profile-form">
                <div class="form-section">
                    <h3>Personal Information</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>First Name</label>
                            <input type="text" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Last Name</label>
                            <input type="text" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Graduation Year</label>
                            <input type="text" name="graduation_year" value="<?php echo htmlspecialchars($user['graduation_year']); ?>">
                        </div>

                        <div class="form-group">
                            <label>Major</label>
                            <input type="text" name="major" value="<?php echo htmlspecialchars($user['major']); ?>">
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3>Professional Information</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Current Position</label>
                            <input type="text" name="current_position" value="<?php echo htmlspecialchars($user['current_position']); ?>">
                        </div>

                        <div class="form-group">
                            <label>Company</label>
                            <input type="text" name="company" value="<?php echo htmlspecialchars($user['company']); ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Bio</label>
                        <textarea name="bio" rows="4" placeholder="Tell us about yourself..."><?php echo htmlspecialchars($user['bio']); ?></textarea>
                    </div>
                </div>

                <button type="submit" class="btn-primary">Save Changes</button>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>