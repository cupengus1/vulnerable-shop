// Placeholder for future JavaScript functionality
document.addEventListener('DOMContentLoaded', function () {
    console.log('Fashion Shop loaded - Educational Security Demo');

    // Add smooth scrolling
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });

    // Warning reminder
    console.warn('⚠️ This is a vulnerable website for educational purposes only!');
});
