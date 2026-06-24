document.addEventListener('DOMContentLoaded', function() {
    var toggleBtn = document.querySelector('.admin-toggle-btn');
    var overlay = document.querySelector('.admin-overlay');
    var body = document.body;

    if (toggleBtn && overlay) {
        toggleBtn.addEventListener('click', function() {
            body.classList.toggle('admin-open');
        });

        overlay.addEventListener('click', function() {
            body.classList.remove('admin-open');
        });
    }
});
