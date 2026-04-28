/**
 * Foufou Ali — Homepage JS
 * Scroll reveal, countdown Flash Promo, newsletter form.
 */
(function () {
    'use strict';

    /* ── Scroll reveal ────────────────────────────── */
    if (typeof IntersectionObserver !== 'undefined') {
        var revealObs = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    revealObs.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

        document.querySelectorAll('.reveal').forEach(function (el) {
            revealObs.observe(el);
        });
    } else {
        // Fallback: show all
        document.querySelectorAll('.reveal').forEach(function (el) {
            el.classList.add('visible');
        });
    }

    /* ── Countdown Flash Promo (jusqu'à minuit) ────── */
    function updateCountdown() {
        var now  = new Date();
        var end  = new Date();
        end.setHours(23, 59, 59, 999);
        var diff = Math.max(0, Math.floor((end - now) / 1000));
        var h = String(Math.floor(diff / 3600)).padStart(2, '0');
        diff %= 3600;
        var m = String(Math.floor(diff / 60)).padStart(2, '0');
        var s = String(diff % 60).padStart(2, '0');
        var hEl = document.getElementById('flash-hours');
        var mEl = document.getElementById('flash-mins');
        var sEl = document.getElementById('flash-secs');
        if (hEl) hEl.textContent = h;
        if (mEl) mEl.textContent = m;
        if (sEl) sEl.textContent = s;
    }
    updateCountdown();
    setInterval(updateCountdown, 1000);

    /* ── Newsletter form ───────────────────────────── */
    var nlForm = document.getElementById('newsletterForm');
    if (nlForm) {
        nlForm.addEventListener('submit', function (e) {
            e.preventDefault();
            var input = nlForm.querySelector('input[type="email"]');
            var btn   = nlForm.querySelector('button');
            var email = input ? input.value.trim() : '';
            if (!email) return;
            btn.textContent = 'Envoi…';
            btn.disabled = true;
            fetch('/wp-admin/admin-ajax.php', {
                method: 'POST',
                body: new URLSearchParams({ action: 'jimee_newsletter', email: email }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).then(function () {
                btn.textContent = 'Merci !';
                input.value = '';
                setTimeout(function () {
                    btn.textContent = "S'abonner →";
                    btn.disabled = false;
                }, 3000);
            }).catch(function () {
                btn.textContent = "S'abonner →";
                btn.disabled = false;
            });
        });
    }

})();
