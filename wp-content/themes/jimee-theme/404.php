<?php
/**
 * 404 Error page.
 */

get_header();
?>
<section class="page-404">
    <div class="page-404__inner">

        <span class="page-404__badge">Erreur 404</span>

        <div class="page-404__number" aria-hidden="true">404</div>

        <h1 class="page-404__title">Page <em>introuvable</em></h1>
        <p class="page-404__desc">La page que vous cherchez n'existe pas ou a été déplacée.<br>Pas de panique, retournez à l'accueil ou explorez nos produits.</p>

        <div class="page-404__actions">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="page-404__btn page-404__btn--primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                Retour à l'accueil
            </a>
            <a href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' ) ); ?>" class="page-404__btn page-404__btn--secondary">
                Explorer la boutique
            </a>
        </div>

        <div class="page-404__search-wrap">
            <p class="page-404__search-label">Ou recherchez directement un produit</p>
            <form action="<?php echo esc_url( home_url( '/' ) ); ?>" method="get" class="page-404__search">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" name="s" placeholder="Sérum vitamine C, crème hydratante…">
                <input type="hidden" name="post_type" value="product">
                <button type="submit">Rechercher</button>
            </form>
        </div>

    </div>
</section>
<?php
get_footer();
