// assets/js/main.js
document.addEventListener('DOMContentLoaded', () => {

    // ---- Live cursor glow (dark theme ambient effect) ----
    const glow = document.createElement('div');
    glow.style.position = 'fixed';
    glow.style.width = '300px';
    glow.style.height = '300px';
    glow.style.borderRadius = '50%';
    glow.style.background = 'radial-gradient(circle, rgba(255,120,73,0.10), transparent 70%)';
    glow.style.pointerEvents = 'none';
    glow.style.zIndex = '0';
    glow.style.transition = 'transform 0.12s ease-out';
    glow.style.left = '0';
    glow.style.top = '0';
    document.body.appendChild(glow);
    document.addEventListener('mousemove', (e) => {
        glow.style.transform = `translate(${e.clientX - 150}px, ${e.clientY - 150}px)`;
    });

    // ---- Animate cards on scroll ----
    const cards = document.querySelectorAll('.blog-card');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(e => {
            if (e.isIntersecting) {
                e.target.classList.add('fade-in-up');
                observer.unobserve(e.target);
            }
        });
    }, { threshold: 0.1 });
    cards.forEach(c => observer.observe(c));

    // ---- Like button (AJAX) ----
    const likeBtn = document.querySelector('.like-btn');
    if (likeBtn) {
        likeBtn.addEventListener('click', () => {
            const blogId = likeBtn.dataset.blogId;
            fetch('ajax_like.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'blog_id=' + encodeURIComponent(blogId)
            })
            .then(r => r.json())
            .then(data => {
                if (data.status === 'ok') {
                    document.getElementById('like-count').textContent = data.count;
                    likeBtn.classList.add('liked');
                    setTimeout(() => likeBtn.classList.remove('liked'), 350);
                    likeBtn.querySelector('.heart').textContent = data.liked ? '❤️' : '🤍';
                } else if (data.status === 'login_required') {
                    window.location.href = 'login.php';
                }
            });
        });
    }

    // ---- Simple client-side form validation ----
    document.querySelectorAll('form[data-validate]').forEach(form => {
        form.addEventListener('submit', function (e) {
            let valid = true;
            form.querySelectorAll('[required]').forEach(input => {
                const errorEl = input.parentElement.querySelector('.error-text');
                if (!input.value.trim()) {
                    valid = false;
                    if (errorEl) errorEl.textContent = 'This field is required';
                } else if (input.type === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(input.value)) {
                    valid = false;
                    if (errorEl) errorEl.textContent = 'Enter a valid email address';
                } else if (input.dataset.minlength && input.value.length < parseInt(input.dataset.minlength)) {
                    valid = false;
                    if (errorEl) errorEl.textContent = `Minimum ${input.dataset.minlength} characters required`;
                } else if (errorEl) {
                    errorEl.textContent = '';
                }
            });

            const pass = form.querySelector('input[name="password"]');
            const confirm = form.querySelector('input[name="confirm_password"]');
            if (pass && confirm && confirm.value !== pass.value) {
                valid = false;
                const errorEl = confirm.parentElement.querySelector('.error-text');
                if (errorEl) errorEl.textContent = 'Passwords do not match';
            }

            if (!valid) e.preventDefault();
        });
    });
});
