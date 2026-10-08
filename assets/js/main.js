document.addEventListener('DOMContentLoaded', function () {
    var toggle = document.querySelector('.nav-toggle');
    var nav = document.querySelector('.main-nav');

    if (toggle && nav) {
        toggle.addEventListener('click', function () {
            var open = nav.classList.toggle('is-open');
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
    }

    var prosperity = document.querySelector('.prosperity-section');
    if (prosperity) {
        if (!('IntersectionObserver' in window)) {
            prosperity.classList.add('is-visible');
            return;
        }

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    prosperity.classList.add('is-visible');
                    observer.disconnect();
                }
            });
        }, { threshold: 0.25 });

        observer.observe(prosperity);
    }

    document.querySelectorAll('.read-more-btn').forEach(function (button) {
        button.addEventListener('click', function () {
            var card = button.closest('.ratna-card');
            if (!card) {
                return;
            }
            var expanded = card.classList.toggle('is-expanded');
            button.textContent = expanded ? 'Show less' : 'Read more';
        });
    });
});
