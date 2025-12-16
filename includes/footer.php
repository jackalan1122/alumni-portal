<footer class="site-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>About</h3>
                    <p>Connecting alumni with career opportunities and networking.</p>
                </div>
                
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="<?php echo SITE_URL; ?>/pages/browse-jobs.php">Browse Jobs</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/pages/events.php">Events</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/pages/alumni.php">Alumni Network</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Contact</h3>
                    <p>Email: info@alumni.com</p>
                    <p>Phone: (555) 123-4567</p>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
    <?php if (isset($include_dashboard_js) && $include_dashboard_js): ?>
    <script src="<?php echo SITE_URL; ?>/assets/js/dashboard.js"></script>
    <?php endif; ?>
</body>
</html>