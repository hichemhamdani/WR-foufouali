<?php
/**
 * Homepage — Layout éditorial Foufou Ali.
 */

get_header();

/* ── Base paths ──────────────────────────────────────── */
$img_base  = JIMEE_URI . '/assets/img/';
$html_imgs = home_url( '/html_version/images/' );

/* ── Hero content (slide 1 from admin) ───────────────── */
$hero_slides = jimee_get_hero_slides();
$slide       = $hero_slides[0] ?? [];
$hero_photo  = ! empty( $slide['image'] )
    ? wp_get_attachment_image_url( $slide['image'], 'full' )
    : $html_imgs . 'homepage-hero-2.png';

/* ── Categories (top 6) ──────────────────────────────── */
$home_cats = get_terms([
    'taxonomy'   => 'product_cat',
    'parent'     => 0,
    'hide_empty' => true,
    'orderby'    => 'count',
    'order'      => 'DESC',
    'number'     => 6,
    'exclude'    => jimee_excluded_cats(),
]);
if ( is_wp_error( $home_cats ) ) $home_cats = [];

/* Overrides positionnels : position => term_id */
$cat_overrides = [ 0 => 893, 1 => 1077, 2 => 685, 3 => 564, 4 => 1688, 5 => 1034 ];
foreach ( $cat_overrides as $pos => $tid ) {
    $t = get_term( $tid, 'product_cat' );
    if ( $t && ! is_wp_error( $t ) ) {
        $home_cats = array_values( array_filter( $home_cats, fn( $c ) => $c->term_id !== $tid ) );
        array_splice( $home_cats, $pos, 0, [ $t ] );
        $home_cats = array_slice( $home_cats, 0, 6 );
    }
}


/* ── On-sale products ────────────────────────────────── */
$on_sale_ids = wc_get_product_ids_on_sale();
$has_sale    = ! empty( $on_sale_ids );
if ( $has_sale ) shuffle( $on_sale_ids );

$promo_query = new WP_Query([
    'post_type'      => 'product',
    'posts_per_page' => 8,
    'post__in'       => $has_sale ? array_slice( $on_sale_ids, 0, 30 ) : [-1],
    'orderby'        => 'post__in',
    'post_status'    => 'publish',
    'meta_query'     => [[ 'key' => '_stock_status', 'value' => 'instock' ]],
]);

$flash_query = $has_sale ? new WP_Query([
    'post_type'      => 'product',
    'posts_per_page' => 3,
    'post__in'       => array_slice( $on_sale_ids, 0, 10 ),
    'orderby'        => 'post__in',
    'post_status'    => 'publish',
    'meta_query'     => [[ 'key' => '_stock_status', 'value' => 'instock' ]],
]) : null;

/* ── Popular products (featured, fallback random) ────── */
$popular_query = new WP_Query([
    'post_type'      => 'product',
    'posts_per_page' => 8,
    'post_status'    => 'publish',
    'orderby'        => 'rand',
    'tax_query'      => [[
        'taxonomy' => 'product_visibility',
        'field'    => 'name',
        'terms'    => 'featured',
    ]],
    'meta_query'     => [[ 'key' => '_stock_status', 'value' => 'instock' ]],
]);
if ( ! $popular_query->have_posts() ) {
    $popular_query = new WP_Query([
        'post_type'      => 'product',
        'posts_per_page' => 8,
        'post_status'    => 'publish',
        'orderby'        => 'rand',
        'meta_query'     => [[ 'key' => '_stock_status', 'value' => 'instock' ]],
    ]);
}

/* ── New arrivals ────────────────────────────────────── */
$new_query = new WP_Query([
    'post_type'      => 'product',
    'posts_per_page' => 4,
    'post_status'    => 'publish',
    'orderby'        => 'date',
    'order'          => 'DESC',
    'meta_query'     => [[ 'key' => '_stock_status', 'value' => 'instock' ]],
    'date_query'     => [[ 'after' => '30 days ago' ]],
]);
if ( ! $new_query->have_posts() ) {
    $new_query = new WP_Query([
        'post_type'      => 'product',
        'posts_per_page' => 4,
        'post_status'    => 'publish',
        'orderby'        => 'date',
        'order'          => 'DESC',
        'meta_query'     => [[ 'key' => '_stock_status', 'value' => 'instock' ]],
    ]);
}

/* ── Brands slider ───────────────────────────────────── */
$curated_brand_ids = [ 3503, 3495, 1570, 3657, 1540, 3683, 3715, 3477, 1550, 3354, 3364, 3716 ];
$home_brands = get_terms([
    'taxonomy'   => 'product_brand',
    'include'    => $curated_brand_ids,
    'orderby'    => 'include',
    'hide_empty' => false,
]);
if ( is_wp_error( $home_brands ) ) $home_brands = [];

/* ── Promo banner ────────────────────────────────────── */
$promo         = jimee_get_promo_banner();
$promo_img_id  = $promo['image'] ?? 0;
$promo_img_url = $promo_img_id
    ? wp_get_attachment_image_url( $promo_img_id, 'large' )
    : $img_base . 'homepage-pub-nobg.png';

/* ── Helper: product card (.pc style) ───────────────── */
if ( ! function_exists( 'hp_pc' ) ) :
function hp_pc( $product_id, $delay = '', $badge_override = null ) {
    $product = wc_get_product( $product_id );
    if ( ! $product ) return;

    $brand_terms = wp_get_post_terms( $product_id, 'product_brand' );
    $brand       = ! empty( $brand_terms ) ? strtoupper( $brand_terms[0]->name ) : '';
    $img_url     = get_the_post_thumbnail_url( $product_id, 'woocommerce_thumbnail' ) ?: wc_placeholder_img_src();
    $on_sale     = $product->is_on_sale();
    $is_new      = ( time() - get_post_time( 'U', false, $product_id ) ) < 30 * DAY_IN_SECONDS;
    $featured    = $product->is_featured();
    $price       = (float) $product->get_price();
    $reg         = (float) $product->get_regular_price();
    $sale_p      = (float) $product->get_sale_price();
    $rating      = (float) $product->get_average_rating();
    $reviews     = (int)   $product->get_review_count();
    $url         = get_permalink( $product_id );
    $title       = get_the_title( $product_id );

    $stock_qty   = $product->managing_stock() ? (int) $product->get_stock_quantity() : null;
    $low_stock   = $stock_qty !== null && $stock_qty > 0 && $stock_qty <= 5;

    if ( $badge_override !== null ) {
        $tag = $badge_override['tag'];
        $tc  = $badge_override['tc'];
    } elseif ( $on_sale && $reg > 0 ) {
        $pct = round( ( 1 - $sale_p / $reg ) * 100 );
        $tag = "−{$pct}%"; $tc = 'tag--orange';
    } elseif ( $low_stock )    { $tag = 'Stock limité'; $tc = 'tag--red'; }
    elseif ( $featured )       { $tag = 'Bestseller';   $tc = 'tag--green'; }
    elseif ( $is_new )         { $tag = 'Nouveau';      $tc = 'tag--green'; }
    else                       { $tag = '';              $tc = ''; }

    $stars = '';
    for ( $i = 0; $i < 5; $i++ ) $stars .= $i < round( $rating ) ? '★' : '☆';

    $display_price = ( $on_sale && $sale_p > 0 )
        ? number_format( $sale_p, 0, ',', ' ' )
        : number_format( $price, 0, ',', ' ' );

    $cls = 'pc reveal' . ( $delay ? ' ' . esc_attr( $delay ) : '' );
    echo '<div class="' . $cls . '">';
    if ( $tag ) echo '<div class="pc__badges"><span class="tag ' . esc_attr( $tc ) . '">' . esc_html( $tag ) . '</span></div>';
    echo '<button class="pc__wish wishlist-btn" data-product-id="' . esc_attr( $product_id ) . '" aria-label="Favoris">🤍</button>';
    echo '<div class="pc__img-wrap">';
    echo '<a href="' . esc_url( $url ) . '" class="pc__img-inner"><img src="' . esc_url( $img_url ) . '" alt="' . esc_attr( $title ) . '" loading="lazy"></a>';
    echo '<div class="pc__overlay"><button class="pc__add cart-btn" data-add-to-cart="' . esc_attr( $product_id ) . '">+ Ajouter au panier</button></div>';
    echo '</div>';
    echo '<div class="pc__body">';
    if ( $brand ) echo '<div class="pc__brand">' . esc_html( $brand ) . '</div>';
    echo '<a href="' . esc_url( $url ) . '" class="pc__name">' . esc_html( $title ) . '</a>';
    if ( $rating > 0 ) echo '<div class="pc__stars">' . $stars . ' <span>(' . $reviews . ')</span></div>';
    echo '<div><span class="pc__price">' . $display_price . ' DA</span>';
    if ( $on_sale && $reg > 0 ) echo '<span class="pc__old">' . number_format( $reg, 0, ',', ' ' ) . ' DA</span>';
    echo '</div></div></div>';
}
endif;
?>

<!-- ═══════════════════════════════════════════════════════════
     1. HERO ÉDITORIAL
════════════════════════════════════════════════════════════ -->
<section class="hero-editorial">
    <div class="container">
        <div class="hero__grid">

            <!-- Grande carte principale (rang 1, pleine largeur) -->
            <div class="hero__main">
                <div class="hero__copy">
                    <?php if ( ! empty( $slide['eyebrow'] ) ) : ?>
                    <div class="hero__eyebrow"><?php echo esc_html( $slide['eyebrow'] ); ?></div>
                    <?php else : ?>
                    <div class="hero__eyebrow">Offre exclusive · Printemps 2026</div>
                    <?php endif; ?>

                    <h1 class="hero__h1">
                        <?php if ( ! empty( $slide['title'] ) ) :
                            echo wp_kses( $slide['title'], [ 'em' => [], 'br' => [] ] );
                        else : ?>
                        Soins <em>premium</em><br>à prix accessible
                        <?php endif; ?>
                    </h1>

                    <p class="hero__sub">
                        <?php echo ! empty( $slide['desc'] )
                            ? esc_html( $slide['desc'] )
                            : 'Jusqu\'à 35% de remise sur une sélection de nos meilleures ventes soins visage et cosmétiques.'; ?>
                    </p>
                    <p class="hero__legal">*Offre valable jusqu'au 30 juin 2026. <a href="<?php echo esc_url( home_url( '/cgv/' ) ); ?>">Voir conditions</a></p>

                    <div class="hero__actions">
                        <a href="<?php echo esc_url( home_url( ! empty( $slide['link'] ) ? $slide['link'] : '/boutique/' ) ); ?>" class="cta cta--solid">
                            <?php echo esc_html( $slide['cta'] ?? 'Découvrir' ); ?>
                            <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" width="14" height="14"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                        </a>
                        <a href="<?php echo esc_url( home_url( '/le-bon-plan-jimee/' ) ); ?>" class="cta cta--outline">Voir les promos</a>
                    </div>
                </div>

                <div class="hero__illus">
                    <img src="<?php echo esc_url( $hero_photo ); ?>" alt="Soins cosmétiques premium" class="hero__illus-photo">
                </div>
            </div>

            <!-- Carte A — Routine Solaire -->
            <div class="hero__card hero__card--a">
                <div class="hero__card-copy">
                    <div class="hero__card-tag">Sélection du moment</div>
                    <div class="hero__card-title">Routine Solaire<br>SPF &amp; Hydratation</div>
                    <div class="hero__card-sub">Protégez et hydratez votre peau avec nos meilleurs soins solaires.</div>
                </div>
                <div class="hero__card-media">
                    <img src="https://images.pexels.com/photos/31552021/pexels-photo-31552021.jpeg?auto=compress&cs=tinysrgb&w=500&q=85" alt="Routine solaire">
                </div>
            </div>

            <!-- Carte B — Promo Corps -->
            <div class="hero__card hero__card--b">
                <div class="hero__card-copy">
                    <div class="hero__card-tag">Offre limitée</div>
                    <div class="hero__card-pct">−30%<span> OFF</span></div>
                    <div class="hero__card-title">Sur tous<br>les soins corps</div>
                </div>
                <div class="hero__card-media">
                    <img src="<?php echo esc_url( $img_base . 'hero-body-care-category-homepage-highlight-2.png' ); ?>" alt="Soins corps">
                </div>
            </div>

        </div><!-- /.hero__grid -->
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════════
     2. TRUST BAR
════════════════════════════════════════════════════════════ -->


<!-- ═══════════════════════════════════════════════════════════
     3. CATÉGORIES
════════════════════════════════════════════════════════════ -->
<?php if ( ! empty( $home_cats ) ) : ?>
<section class="cats">
    <div class="container">
        <div class="sh reveal">
            <div>
                <div class="sh__title">Nos <em>Catégories</em></div>
                <div class="sh__sub">Trouvez rapidement ce dont vous avez besoin</div>
            </div>
            <a href="<?php echo esc_url( home_url( '/boutique/' ) ); ?>" class="sh__link">Tout explorer →</a>
        </div>

        <div class="cats__grid">
            <?php
            $cat_classes = [ 'cat--wide', 'cat--tall', '', '', '', '' ];
            foreach ( $home_cats as $i => $cat ) :
                $thumbnail_id = get_term_meta( $cat->term_id, 'thumbnail_id', true );
                $photo        = $thumbnail_id ? wp_get_attachment_image_url( $thumbnail_id, 'large' ) : '';
                $cls     = isset( $cat_classes[ $i ] ) ? $cat_classes[ $i ] : '';
                $num_cls = 'cat--' . ( $i + 1 );
                $delay   = $i > 0 ? ' reveal-delay-' . min( $i, 5 ) : '';
            ?>
            <a href="<?php echo esc_url( get_term_link( $cat ) ); ?>"
               class="cat <?php echo esc_attr( $num_cls . ' ' . $cls ); ?> reveal<?php echo $delay; ?>">
                <?php if ( $photo ) : ?>
                <img src="<?php echo esc_url( $photo ); ?>"
                     alt="<?php echo esc_attr( $cat->name ); ?>"
                     class="cat__photo" loading="lazy">
                <?php endif; ?>
                <div class="cat__name"><?php echo esc_html( $cat->name ); ?></div>
                <div class="cat__count"><?php echo $cat->count; ?> produits</div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ═══════════════════════════════════════════════════════════
     4. PRODUITS EN PROMOTION
════════════════════════════════════════════════════════════ -->
<?php if ( $promo_query->have_posts() ) : ?>
<section class="products section--tinted">
    <div class="container">
        <div class="sh reveal">
            <div>
                <div class="sh__title">Produits en <em>Promotions</em></div>
                <div class="sh__sub">Les mieux notés par nos clients</div>
            </div>
            <a href="<?php echo esc_url( home_url( '/le-bon-plan-jimee/' ) ); ?>" class="sh__link">Voir tout →</a>
        </div>

        <div class="prod-grid">
            <?php
            $delays = [ '', 'reveal-delay-1', 'reveal-delay-2', 'reveal-delay-3', 'reveal-delay-4', '', 'reveal-delay-1', 'reveal-delay-2' ];
            $B = [ 'best' => ['tag'=>'Bestseller','tc'=>'tag--green'], 'new' => ['tag'=>'Nouveau','tc'=>'tag--green'], 'lim' => ['tag'=>'Stock limité','tc'=>'tag--red'] ];
            $promo_badges = [ null, $B['best'], null, $B['new'], null, $B['lim'], null, $B['best'] ];
            $di = 0;
            while ( $promo_query->have_posts() ) :
                $promo_query->the_post();
                hp_pc( get_the_ID(), $delays[ $di ] ?? '', $promo_badges[ $di ] ?? null );
                $di++;
            endwhile;
            wp_reset_postdata();
            ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ═══════════════════════════════════════════════════════════
     5. FLASH PROMO (avec countdown)
════════════════════════════════════════════════════════════ -->
<?php if ( $flash_query && $flash_query->have_posts() ) : ?>
<div class="flash-wrap">
    <div class="container">
        <div class="flash reveal">
            <div class="flash__left">
                <div class="flash__badge">⚡ Flash Promo</div>
                <div class="flash__title">Offres du jour<br>jusqu'à −50%</div>
                <div class="flash__sub">Réductions limitées avant<br>épuisement du stock</div>
                <div class="flash__timer">
                    <div class="flash__unit">
                        <span class="flash__num" id="flash-hours">00</span>
                        <span class="flash__lbl">Heures</span>
                    </div>
                    <span class="flash__sep">:</span>
                    <div class="flash__unit">
                        <span class="flash__num" id="flash-mins">00</span>
                        <span class="flash__lbl">Minutes</span>
                    </div>
                    <span class="flash__sep">:</span>
                    <div class="flash__unit">
                        <span class="flash__num" id="flash-secs">00</span>
                        <span class="flash__lbl">Secondes</span>
                    </div>
                </div>
                <a href="<?php echo esc_url( home_url( '/le-bon-plan-jimee/' ) ); ?>" class="flash__see-all">Voir toutes les promos →</a>
            </div>

            <div class="flash__products">
                <?php while ( $flash_query->have_posts() ) : $flash_query->the_post();
                    $fp      = wc_get_product( get_the_ID() );
                    if ( ! $fp ) continue;
                    $fp_img  = get_the_post_thumbnail_url( get_the_ID(), 'woocommerce_thumbnail' ) ?: wc_placeholder_img_src();
                    $fp_reg  = (float) $fp->get_regular_price();
                    $fp_sale = (float) $fp->get_sale_price();
                    $fp_pct  = $fp_reg > 0 ? round( ( 1 - $fp_sale / $fp_reg ) * 100 ) : 0;
                    $fp_brand_terms = wp_get_post_terms( get_the_ID(), 'product_brand' );
                    $fp_brand = ! empty( $fp_brand_terms ) ? strtoupper( $fp_brand_terms[0]->name ) : '';
                    $fp_stock = rand( 15, 75 );
                ?>
                <a href="<?php echo esc_url( get_permalink() ); ?>" class="flash__prod">
                    <?php if ( $fp_pct > 0 ) : ?>
                    <span class="flash__prod-badge">−<?php echo $fp_pct; ?>%</span>
                    <?php endif; ?>
                    <div class="flash__prod-img">
                        <img src="<?php echo esc_url( $fp_img ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>">
                    </div>
                    <?php if ( $fp_brand ) : ?>
                    <div class="flash__prod-brand"><?php echo esc_html( $fp_brand ); ?></div>
                    <?php endif; ?>
                    <div class="flash__prod-name"><?php echo esc_html( wp_trim_words( get_the_title(), 6 ) ); ?></div>
                    <div class="flash__prod-prices">
                        <span class="flash__prod-price"><?php echo number_format( $fp_sale ?: $fp->get_price(), 0, ',', ' ' ); ?> DA</span>
                        <?php if ( $fp_reg > 0 ) : ?>
                        <span class="flash__prod-old"><?php echo number_format( $fp_reg, 0, ',', ' ' ); ?> DA</span>
                        <?php endif; ?>
                    </div>
                    <div class="flash__prod-stock">
                        <div class="flash__prod-stock-bar">
                            <div class="flash__prod-stock-fill" style="width:<?php echo $fp_stock; ?>%"></div>
                        </div>
                        <span class="flash__prod-stock-lbl"><?php echo $fp_stock < 20 ? 'Stock critique' : $fp_stock . '% restants'; ?></span>
                    </div>
                </a>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>

        </div>
    </div>
</div>
<?php endif; ?>

<!-- ═══════════════════════════════════════════════════════════
     6. PRODUITS POPULAIRES
════════════════════════════════════════════════════════════ -->
<?php if ( $popular_query->have_posts() ) : ?>
<section class="products">
    <div class="container">
        <div class="sh reveal">
            <div>
                <div class="sh__title">Produits <em>Populaires</em></div>
                <div class="sh__sub">Les mieux notés par nos clients</div>
            </div>
            <a href="<?php echo esc_url( home_url( '/boutique/' ) ); ?>" class="sh__link">Voir tout →</a>
        </div>

        <div class="prod-grid">
            <?php
            $delays = [ '', 'reveal-delay-1', 'reveal-delay-2', 'reveal-delay-3', 'reveal-delay-4', '', 'reveal-delay-1', 'reveal-delay-2' ];
            $B = [ 'best' => ['tag'=>'Bestseller','tc'=>'tag--green'], 'new' => ['tag'=>'Nouveau','tc'=>'tag--green'], 'lim' => ['tag'=>'Stock limité','tc'=>'tag--red'] ];
            $popular_badges = [ $B['best'], null, $B['new'], null, $B['lim'], $B['best'], null, $B['new'] ];
            $di = 0;
            while ( $popular_query->have_posts() ) :
                $popular_query->the_post();
                hp_pc( get_the_ID(), $delays[ $di ] ?? '', $popular_badges[ $di ] ?? null );
                $di++;
            endwhile;
            wp_reset_postdata();
            ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ═══════════════════════════════════════════════════════════
     7. BANNIÈRE PROMO (split 50/50)
════════════════════════════════════════════════════════════ -->
<section class="pb-section">
    <div class="container">
        <div class="pb reveal">
            <div class="pb__content">
                <div class="pb__tag"><?php echo esc_html( $promo['eyebrow'] ?? 'Soins naturels & Bio' ); ?></div>
                <h2 class="pb__title">
                    <?php echo wp_kses( $promo['title'] ?? 'Des soins qui<br>révèlent votre<br>éclat naturel', [ 'br' => [], 'em' => [] ] ); ?>
                </h2>
                <p class="pb__desc">
                    <?php echo esc_html( $promo['desc'] ?? 'Crèmes, sérums et soins formulés avec des actifs naturels pour nourrir, hydrater et protéger votre peau au quotidien.' ); ?>
                </p>
                <a href="<?php echo esc_url( home_url( $promo['link'] ?? '/le-bon-plan-jimee/' ) ); ?>" class="pb__cta">
                    <?php echo esc_html( $promo['cta'] ?? 'Découvrir la sélection' ); ?> →
                </a>
            </div>
            <div class="pb__media">
                <img src="<?php echo esc_url( $promo_img_url ); ?>"
                     alt="Soins visage naturels"
                     class="pb__photo">
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════════
     8. NOUVEAUTÉS
════════════════════════════════════════════════════════════ -->
<?php if ( $new_query->have_posts() ) : ?>
<section class="products" style="padding-top:0">
    <div class="container">
        <div class="sh reveal">
            <div>
                <div class="sh__title">Nos <em>Nouveautés</em></div>
                <div class="sh__sub">Tout juste arrivé en stock</div>
            </div>
            <a href="<?php echo esc_url( home_url( '/boutique/' ) ); ?>" class="sh__link">Voir tout →</a>
        </div>

        <div class="prod-grid">
            <?php
            $delays = [ '', 'reveal-delay-1', 'reveal-delay-2', 'reveal-delay-3' ];
            $B = [ 'best' => ['tag'=>'Bestseller','tc'=>'tag--green'], 'new' => ['tag'=>'Nouveau','tc'=>'tag--green'], 'lim' => ['tag'=>'Stock limité','tc'=>'tag--red'] ];
            $new_badges = [ $B['new'], null, $B['best'], $B['lim'] ];
            $di = 0;
            while ( $new_query->have_posts() ) :
                $new_query->the_post();
                hp_pc( get_the_ID(), $delays[ $di ] ?? '', $new_badges[ $di ] ?? null );
                $di++;
            endwhile;
            wp_reset_postdata();
            ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ═══════════════════════════════════════════════════════════
     9. MARQUES PARTENAIRES
════════════════════════════════════════════════════════════ -->
<?php if ( ! empty( $home_brands ) ) : ?>
<div class="brands">
    <div class="container">
        <div class="sh reveal">
            <div>
                <div class="sh__title">Nos <em>Marques</em> Partenaires</div>
                <div class="sh__sub">+150 marques certifiées disponibles</div>
            </div>
            <a href="<?php echo esc_url( home_url( '/marques/' ) ); ?>" class="sh__link">Toutes les marques →</a>
        </div>

        <div class="brands__row">
            <?php foreach ( $home_brands as $brand ) :
                $logo_id  = get_term_meta( $brand->term_id, 'pharma_logo_square', true );
                $logo_url = $logo_id ? wp_get_attachment_image_url( $logo_id, 'thumbnail' ) : '';
            ?>
            <a href="<?php echo esc_url( get_term_link( $brand ) ); ?>" class="brand-card" title="<?php echo esc_attr( $brand->name ); ?>">
                <?php if ( $logo_url ) : ?>
                    <img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( $brand->name ); ?>" loading="lazy">
                <?php else : ?>
                    <span style="font-size:11px;font-weight:700;color:#888;text-align:center;letter-spacing:0.5px"><?php echo esc_html( $brand->name ); ?></span>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<section class="trust">
    <div class="container">
        <div class="trust__inner">

            <div class="trust__item reveal">
                <div class="trust__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="1" y="3" width="15" height="13" rx="2"/><path d="M16 8h4l3 3v5a2 2 0 0 1-2 2h-1"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                </div>
                <div class="trust__title">Livraison rapide</div>
                <div class="trust__sub">Recevez vos commandes en 48h partout en Algérie.</div>
            </div>

            <div class="trust__item reveal reveal-delay-1">
                <div class="trust__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </div>
                <div class="trust__title">Paiement sécurisé</div>
                <div class="trust__sub">CIB, Edahabia, virement ou paiement à la livraison.</div>
            </div>

            <div class="trust__item reveal reveal-delay-2">
                <div class="trust__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/></svg>
                </div>
                <div class="trust__title">Produits Certifiés</div>
                <div class="trust__sub">100% authentiques, issus des distributeurs officiels.</div>
            </div>

            <div class="trust__item reveal reveal-delay-3">
                <div class="trust__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="20 12 20 22 4 22 4 12"/><rect x="2" y="7" width="20" height="5" rx="1"/><line x1="12" y1="22" x2="12" y2="7"/><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"/><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/></svg>
                </div>
                <div class="trust__title">Retour Facile</div>
                <div class="trust__sub">7 jours pour changer d'avis, retour simple et gratuit.</div>
            </div>

        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════════
     10. NEWSLETTER
════════════════════════════════════════════════════════════ -->
<div class="container">
    <div class="hp-newsletter reveal">
        <div class="hp-newsletter__tag"><span class="tag tag--white">Newsletter</span></div>
        <div class="hp-newsletter__title">Restez <em>informé</em> des meilleures offres</div>
        <div class="hp-newsletter__sub">Recevez promotions exclusives et nouveautés directement dans votre boîte mail</div>
        <form class="hp-newsletter__form" id="newsletterForm">
            <input class="hp-newsletter__input" type="email" placeholder="Votre adresse email…" required>
            <button type="submit" class="hp-newsletter__btn">S'abonner →</button>
        </form>
        <div class="hp-newsletter__note">Pas de spam. Désinscription en 1 clic.</div>
    </div>
</div>

<!-- JSON-LD LocalBusiness Schema -->
<script type="application/ld+json">
<?php
echo wp_json_encode( [
    '@context'    => 'https://schema.org',
    '@type'       => 'CosmeticsStore',
    'name'        => get_bloginfo( 'name' ),
    'url'         => home_url( '/' ),
    'description' => 'Parapharmacie et cosmétiques en ligne. Produits 100% authentiques, livrés partout en Algérie.',
    'address'     => [
        '@type'          => 'PostalAddress',
        'addressCountry' => 'DZ',
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
?>
</script>

<?php get_footer(); ?>
