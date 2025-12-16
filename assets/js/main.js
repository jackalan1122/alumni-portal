// Mobile menu toggle
document.addEventListener('DOMContentLoaded', function() {
    // Add any global JavaScript functionality here
    console.log('Alumni Portal loaded');

    // Mobile menu functionality
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const mainNavigation = document.querySelector('.main-navigation');

    if (mobileMenuToggle && mainNavigation) {
        mobileMenuToggle.addEventListener('click', function() {
            mobileMenuToggle.classList.toggle('active');
            mainNavigation.classList.toggle('active');
        });

        // Close mobile menu when clicking on a link
        const navLinks = mainNavigation.querySelectorAll('a');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                mobileMenuToggle.classList.remove('active');
                mainNavigation.classList.remove('active');
            });
        });

        // Close mobile menu when clicking outside
        document.addEventListener('click', function(event) {
            if (!mobileMenuToggle.contains(event.target) && !mainNavigation.contains(event.target)) {
                mobileMenuToggle.classList.remove('active');
                mainNavigation.classList.remove('active');
            }
        });
    }
});