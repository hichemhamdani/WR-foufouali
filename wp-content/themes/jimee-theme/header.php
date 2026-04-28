<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<!-- TOP BAR -->
<div class="topbar">
    <span class="topbar__msg">
        <strong>Soldes Printemps</strong>
        <span class="topbar__dot"></span>
        Jusqu'à <strong>−35%</strong> sur une sélection de soins visage et cosmétiques
        <span class="topbar__dot"></span>
        Code <strong>FOUFOU26</strong>
    </span>
</div>

<!-- HEADER (sticky) -->
<header class="header" id="header">
    <div class="container">

        <!-- Main row: Logo | Search | Actions -->
        <div class="header__main">

            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="header__logo">Foufou <em>Ali</em></a>

            <!-- Search bar + live dropdown -->
            <div class="header__search" id="searchWrapper">
                <form action="<?php echo esc_url( home_url( '/' ) ); ?>" method="get" id="searchBar">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" name="s" id="headerSearchInput" placeholder="Rechercher un produit, une marque…" autocomplete="off">
                    <input type="hidden" name="post_type" value="product">
                </form>
                <div class="search-dropdown" id="searchDropdown">
                    <div class="search-dropdown-suggestions" id="searchSuggestions">
                        <div class="search-dropdown-title">Recherches populaires</div>
                        <a href="<?php echo esc_url( home_url( '/?s=sérum+vitamine+c&post_type=product' ) ); ?>" class="search-dropdown-item">Sérum vitamine C</a>
                        <a href="<?php echo esc_url( home_url( '/?s=crème+hydratante&post_type=product' ) ); ?>" class="search-dropdown-item">Crème hydratante</a>
                        <a href="<?php echo esc_url( home_url( '/?s=huile+rose+musquée&post_type=product' ) ); ?>" class="search-dropdown-item">Huile de rose musquée</a>
                        <a href="<?php echo esc_url( home_url( '/?s=masque+cheveux&post_type=product' ) ); ?>" class="search-dropdown-item">Masque cheveux</a>
                        <a href="<?php echo esc_url( home_url( '/?s=spf+50&post_type=product' ) ); ?>" class="search-dropdown-item">SPF 50</a>
                        <a href="<?php echo esc_url( home_url( '/?s=niacinamide&post_type=product' ) ); ?>" class="search-dropdown-item">Niacinamide</a>
                    </div>
                    <div class="search-dropdown-results" id="searchResults" style="display:none"></div>
                    <a href="#" class="search-dropdown-all" id="searchAllLink" style="display:none">Voir tous les résultats</a>
                </div>
            </div>

            <!-- Right actions -->
            <div class="header__actions">
                <a href="<?php echo esc_url( home_url( '/nos-adresses/' ) ); ?>" class="header__action-group" title="Nos adresses">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    <span class="header__action-label"><small>Trouver</small>Nos adresses</span>
                </a>
                <div class="header__divider"></div>
                <a href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : '#' ); ?>" class="header__action-group" title="Mon compte">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <span class="header__action-label"><small>Bonjour</small>Se connecter</span>
                </a>
                <div class="header__divider"></div>
                <a href="<?php echo esc_url( home_url( '/wishlist/' ) ); ?>" class="header__icon-only wishlist-header-btn" aria-label="Favoris">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                    <span class="wishlist-count" id="wishlistCount" style="display:none">0</span>
                </a>
                <button class="header__icon-only cart-toggle-btn" id="cartToggleBtn" aria-label="Panier">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                    <span class="cart-count" id="cartCount"><?php echo function_exists( 'WC' ) && WC()->cart ? WC()->cart->get_cart_contents_count() : '0'; ?></span>
                </button>
            </div>

        </div><!-- /.header__main -->

        <!-- Navigation -->
        <nav class="nav" aria-label="Navigation principale">
            <div class="nav__inner">
                <button class="nav__menu" id="menuToggle" aria-label="Menu">
                    <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                    Menu
                </button>
                <?php
                $nav_cats = get_terms([
                    'taxonomy'   => 'product_cat',
                    'parent'     => 0,
                    'hide_empty' => true,
                    'orderby'    => 'meta_value_num',
                    'meta_key'   => 'order',
                    'order'      => 'ASC',
                    'exclude'    => jimee_excluded_cats(),
                    'number'     => 8,
                ]);
                $current_term_id = is_product_category() ? get_queried_object_id() : 0;
                if ( ! is_wp_error( $nav_cats ) ) {
                    foreach ( $nav_cats as $cat ) {
                        if ( in_array( $cat->slug, [ 'uncategorized', 'non-classe', 'non-categorise' ], true ) ) continue;
                        $active_class = ( $cat->term_id === $current_term_id ) ? ' nav__item--active' : '';
                        printf(
                            '<a href="%s" class="nav__item%s">%s</a>',
                            esc_url( get_term_link( $cat ) ),
                            $active_class,
                            esc_html( $cat->name )
                        );
                    }
                }
                ?>
                <div class="nav__sep"></div>
                <a href="<?php echo esc_url( home_url( '/le-bon-plan-jimee/' ) ); ?>" class="nav__item nav__item--promos">Bons Plans &amp; Promos</a>
            </div>
        </nav>

    </div>
</header>

<?php get_template_part( 'template-parts/mobile-menu' ); ?>
<?php get_template_part( 'template-parts/side-cart' ); ?>
<?php get_template_part( 'template-parts/search-overlay' ); ?>

<main id="main-content">
