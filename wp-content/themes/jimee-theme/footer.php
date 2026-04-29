</main><!-- #main-content -->

<!-- FOOTER -->
<footer class="footer">
    <div class="container">
        <div class="footer__top">

            <!-- Colonne 1 : Logo + tagline -->
            <div>
                <div class="footer__brand">Foufou <em>Ali</em></div>
                <p class="footer__desc">Votre cosmétique de confiance. Produits 100% authentiques, livrés partout en Algérie.</p>
                <div class="footer__social">
                    <a href="https://www.facebook.com/foufoualicosmetics" target="_blank" rel="noopener" aria-label="Facebook" class="footer__soc">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
                    </a>
                    <a href="https://www.instagram.com/foufouali" target="_blank" rel="noopener" aria-label="Instagram" class="footer__soc">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="5"/><circle cx="17.5" cy="6.5" r="1.5" fill="currentColor" stroke="none"/></svg>
                    </a>
                    <a href="https://www.tiktok.com/@foufouali" target="_blank" rel="noopener" aria-label="TikTok" class="footer__soc">
                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-2.88 2.5 2.89 2.89 0 0 1 0-5.78 2.92 2.92 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 3 15.57 6.33 6.33 0 0 0 9.37 22a6.33 6.33 0 0 0 6.38-6.2V9.06a8.16 8.16 0 0 0 3.84.96V6.69z"/></svg>
                    </a>
                </div>
            </div>

            <!-- Colonne 2 : Contact -->
            <div>
                <div class="footer__contact-label">Besoin d'aide ?</div>
                <div class="footer__contact-phone">0560 00 00 00</div>
                <div class="footer__contact-lines">
                    <strong>Conseil :</strong> 0560 00 00 01<br>
                    <strong>Administration :</strong> 0560 00 00 02
                </div>
                <div class="footer__contact-address">Alger, Algérie</div>
                <div class="footer__contact-hours-title">Horaires</div>
                <div class="footer__contact-hours">
                    Dim – Jeu : 08h – 20h<br>
                    Ven : 09h – 12h et 15h – 20h<br>
                    Sam : 09h – 20h
                </div>
            </div>

            <div>
                <div class="footer__col-title">Boutique</div>
                <ul class="footer__links">
                    <?php
                    $footer_cats = get_terms([
                        'taxonomy'   => 'product_cat',
                        'parent'     => 0,
                        'hide_empty' => true,
                        'number'     => 6,
                        'orderby'    => 'meta_value_num',
                        'meta_key'   => 'order',
                        'order'      => 'ASC',
                        'exclude'    => jimee_excluded_cats(),
                    ]);
                    if ( ! is_wp_error( $footer_cats ) ) {
                        foreach ( $footer_cats as $cat ) {
                            if ( in_array( $cat->slug, [ 'uncategorized', 'non-classe', 'non-categorise' ], true ) ) continue;
                            printf( '<li><a href="%s">%s</a></li>', esc_url( get_term_link( $cat ) ), esc_html( $cat->name ) );
                        }
                    }
                    ?>
                </ul>
            </div>

            <div class="footer__col--service">
                <div class="footer__col-title">Service client</div>
                <ul class="footer__links">
                    <li><a href="<?php echo esc_url( home_url( '/suivi-commande/' ) ); ?>">Suivi de commande</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/livraison/' ) ); ?>">Livraison &amp; délais</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/retours/' ) ); ?>">Retours</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/a-propos/#faq' ) ); ?>">FAQ</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Nous contacter</a></li>
                </ul>
            </div>

            <div>
                <div class="footer__col-title">À propos</div>
                <ul class="footer__links">
                    <li><a href="<?php echo esc_url( home_url( '/a-propos/' ) ); ?>">Qui sommes-nous</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/marques/' ) ); ?>">Nos marques</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/mentions-legales/' ) ); ?>">Mentions légales</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/conditions-de-vente/' ) ); ?>">CGV</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/politique-de-confidentialite/' ) ); ?>">Confidentialité</a></li>
                </ul>
            </div>
        </div>

        <div class="footer__bottom">
            <div>&copy; <?php echo date( 'Y' ); ?> Foufou Ali &mdash; Tous droits réservés. Conçu par <a href="https://web-rocket.dz" target="_blank" rel="noopener">Web Rocket</a></div>
            <div class="footer__pay">
                <div class="footer__pay-icon">CIB</div>
                <div class="footer__pay-icon">Edahabia</div>
                <div class="footer__pay-icon">Cash</div>
            </div>
        </div>
    </div>
</footer>

<!-- POPUP COUPON (first visit, on scroll) -->
<div class="popup-overlay" id="popupOverlay">
    <div class="popup-card">
        <button class="popup-close" id="popupClose" aria-label="Fermer">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
        <div class="popup-badge">Offre de bienvenue</div>
        <h2 class="popup-title">-15% sur votre <em>première commande</em></h2>
        <p class="popup-desc">Utilisez le code ci-dessous lors de votre passage en caisse pour profiter de la remise.</p>
        <div class="popup-code" id="popupCode">FOUFOU15</div>
        <button class="popup-copy" id="popupCopy">Copier le code</button>
        <p class="popup-note">Valable une seule fois, sur tout le site.</p>
    </div>
</div>
<style>
.popup-overlay{position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:10000;display:none;align-items:center;justify-content:center;padding:20px;opacity:0;transition:opacity .3s}
.popup-overlay.active{display:flex;opacity:1}
.popup-card{background:#fff;border-radius:24px;padding:40px 32px;max-width:420px;width:100%;text-align:center;position:relative;transform:translateY(20px);transition:transform .4s cubic-bezier(.16,1,.3,1)}
.popup-overlay.active .popup-card{transform:translateY(0)}
.popup-close{position:absolute;top:16px;right:16px;width:36px;height:36px;border-radius:50%;background:#f5f4ee;display:flex;align-items:center;justify-content:center;border:none;cursor:pointer;transition:background .2s}
.popup-close:hover{background:#eee}
.popup-badge{display:inline-block;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:1px;color:#064A2A;background:rgba(6,74,42,.08);padding:6px 16px;border-radius:99px;margin-bottom:16px}
.popup-title{font-size:24px;font-weight:300;line-height:1.3;margin:0 0 12px}
.popup-title em{font-weight:700;font-style:italic}
.popup-desc{font-size:14px;color:#666;line-height:1.6;margin:0 0 20px}
.popup-code{font-family:'Poppins',sans-serif;font-size:28px;font-weight:700;letter-spacing:4px;background:#f5f4ee;border:2px dashed #71ac1e;border-radius:12px;padding:14px 24px;margin:0 auto 16px;display:inline-block;user-select:all}
.popup-copy{background:#064A2A;color:#fff;border:none;border-radius:99px;padding:12px 32px;font-size:14px;font-weight:600;font-family:'Poppins',sans-serif;cursor:pointer;transition:background .2s}
.popup-copy:hover{background:#053d22}
.popup-note{font-size:12px;color:#999;margin:12px 0 0}
</style>
<script>
(function(){
    if(document.cookie.indexOf('foufou_popup_seen')!==-1) return;
    if(localStorage.getItem('foufou_popup_seen')) return;

    var shown = false;
    function closePopup(){
        var o = document.getElementById('popupOverlay');
        o.classList.remove('active');
        setTimeout(function(){ o.style.display='none'; },300);
    }
    function showPopup(){
        if(shown) return;
        var scrollPct = window.scrollY/(document.body.scrollHeight-window.innerHeight);
        if(scrollPct<0.25) return;
        shown=true;
        window.removeEventListener('scroll',showPopup);
        var overlay=document.getElementById('popupOverlay');
        overlay.style.display='flex';
        requestAnimationFrame(function(){ overlay.classList.add('active'); });
        document.cookie='foufou_popup_seen=1;path=/;max-age='+(30*86400);
        localStorage.setItem('foufou_popup_seen','1');
    }
    window.addEventListener('scroll',showPopup,{passive:true});

    document.getElementById('popupClose').addEventListener('click',closePopup);
    document.getElementById('popupOverlay').addEventListener('click',function(e){
        if(e.target===this) closePopup();
    });
    document.getElementById('popupCopy').addEventListener('click',function(){
        navigator.clipboard.writeText('FOUFOU15');
        this.textContent='Code copié !';
        var btn=this;
        setTimeout(function(){ btn.textContent='Copier le code'; },2000);
    });
})();
</script>

<?php wp_footer(); ?>
</body>
</html>
