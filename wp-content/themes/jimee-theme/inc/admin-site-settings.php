<?php
/**
 * Gestion du site — page d'administration unifiée.
 * Tabs : Popup Promo | Bannières | Coming soon | Formulaire
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ── Menu ─────────────────────────────────────────────── */
add_action( 'admin_menu', 'jimee_site_menu' );
function jimee_site_menu() {
    add_menu_page(
        'Gestion du site',
        'Gestion du site',
        'manage_woocommerce',
        'jimee-site',
        'jimee_site_page',
        'dashicons-admin-settings',
        58
    );
}

/* ── Media uploader ───────────────────────────────────── */
add_action( 'admin_enqueue_scripts', 'jimee_site_enqueue' );
function jimee_site_enqueue( $hook ) {
    if ( $hook !== 'toplevel_page_jimee-site' ) return;
    wp_enqueue_media();
}

/* ── Formulaire setting ───────────────────────────────── */
add_action( 'admin_init', 'jimee_formulaire_register' );
function jimee_formulaire_register() {
    register_setting( 'jimee_formulaire', 'jimee_contact_email', [
        'sanitize_callback' => 'sanitize_email',
    ] );
}

/* ── Page HTML ────────────────────────────────────────── */
function jimee_site_page() {
    if ( ! current_user_can( 'manage_woocommerce' ) ) wp_die( 'Accès refusé.' );

    $tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'popup';
    $tabs = [
        'popup'       => 'Popup Promo',
        'banners'     => 'Bannières',
        'coming-soon' => 'Coming soon',
        'formulaire'  => 'Formulaire',
    ];
    ?>
    <div class="wrap">
        <h1 style="font-size:26px;font-weight:300;margin-bottom:4px">Gestion <strong>du site</strong></h1>
        <p style="color:#666;margin-top:4px;margin-bottom:0">Gérez les paramètres clés du site depuis cette interface.</p>

        <?php settings_errors(); ?>

        <nav class="nav-tab-wrapper" style="margin:16px 0 24px">
            <?php foreach ( $tabs as $key => $label ) : ?>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=jimee-site&tab=' . $key ) ); ?>"
                   class="nav-tab<?php echo $tab === $key ? ' nav-tab-active' : ''; ?>">
                    <?php echo esc_html( $label ); ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <?php
        switch ( $tab ) {
            case 'popup':       jimee_site_tab_popup();       break;
            case 'banners':     jimee_site_tab_banners();     break;
            case 'coming-soon': jimee_site_tab_cs();          break;
            case 'formulaire':  jimee_site_tab_formulaire();  break;
        }
        ?>
    </div>
    <?php
}

/* ── Tab : Popup Promo ────────────────────────────────── */
function jimee_site_tab_popup(): void {
    $o          = jimee_popup_options();
    $bg_url     = $o['bg_img_id'] ? wp_get_attachment_image_url( $o['bg_img_id'], 'large' ) : '';
    $default_bg = get_template_directory_uri() . '/assets/img/masque-visage-routine-skincare.jpg';
    $preview_bg = $bg_url ?: $default_bg;
    ?>
    <form method="post" action="options.php" style="max-width:720px">
        <?php settings_fields( 'jimee_popup_group' ); ?>

        <div style="display:flex;flex-direction:column;gap:24px">

            <!-- Activer -->
            <div style="background:#fff;border:1px solid #ddd;border-radius:8px;padding:20px 24px">
                <label style="display:flex;align-items:center;gap:12px;cursor:pointer">
                    <input type="checkbox" name="jimee_popup[enabled]" value="1" <?php checked( $o['enabled'], 1 ); ?> style="width:18px;height:18px">
                    <span style="font-size:15px;font-weight:600">Activer la popup</span>
                </label>
                <p style="margin:8px 0 0 30px;color:#888;font-size:13px">Si décoché, la popup ne s'affiche plus sur le site.</p>
            </div>

            <!-- Image de fond -->
            <div style="background:#fff;border:1px solid #ddd;border-radius:8px;padding:20px 24px">
                <h2 style="font-size:14px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:#555;margin:0 0 16px">Image de fond</h2>
                <input type="hidden" name="jimee_popup[bg_img_id]" id="jimee_popup_bg_img_id" value="<?php echo esc_attr( $o['bg_img_id'] ); ?>">
                <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap">
                    <div id="jimee-popup-bg-preview" style="width:120px;height:80px;border-radius:8px;overflow:hidden;border:1px solid #ddd;background:#f5f5f5;display:flex;align-items:center;justify-content:center">
                        <?php if ( $bg_url ) : ?>
                            <img src="<?php echo esc_url( $bg_url ); ?>" style="width:100%;height:100%;object-fit:cover">
                        <?php else : ?>
                            <span style="font-size:22px;color:#ccc">🖼</span>
                        <?php endif; ?>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:8px">
                        <button type="button" id="jimee-popup-bg-select" class="button">Choisir une image</button>
                        <button type="button" id="jimee-popup-bg-remove" class="button button-link-delete" <?php echo $bg_url ? '' : 'style="display:none"'; ?>>Supprimer</button>
                    </div>
                </div>
                <p style="margin:10px 0 0;color:#888;font-size:12px">Si aucune image choisie, une image par défaut sera utilisée.</p>
            </div>

            <!-- Contenu -->
            <div style="background:#fff;border:1px solid #ddd;border-radius:8px;padding:20px 24px">
                <h2 style="font-size:14px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:#555;margin:0 0 16px">Contenu</h2>
                <?php jimee_popup_field( 'badge', 'Sous-titre (petit texte au-dessus)', $o['badge'], 'text', 'ex : Offre limitée' ); ?>
                <?php jimee_popup_field( 'title', 'Titre (partie normale)', $o['title'], 'text', 'ex : -15%' ); ?>
                <?php jimee_popup_field( 'title_em', 'Titre (partie en italique)', $o['title_em'], 'text', 'ex : sur votre commande' ); ?>
                <?php jimee_popup_field( 'desc', 'Description (avant le code)', $o['desc'], 'textarea', 'ex : Utilisez votre code promo à la caisse :' ); ?>
                <?php jimee_popup_field( 'code', 'Code promo (affiché en gras après la description)', $o['code'], 'text', 'ex : FOUFOU15' ); ?>
                <?php jimee_popup_field( 'cta_text', 'Texte du bouton', $o['cta_text'], 'text', 'ex : Voir la boutique' ); ?>
                <?php jimee_popup_field( 'cta_url', 'Lien du bouton (vide = copie le code)', $o['cta_url'], 'url', 'ex : https://foufouali.dz/boutique/' ); ?>
                <?php jimee_popup_field( 'note', 'Note (petit texte bas)', $o['note'], 'text', 'ex : Valable une seule fois, sur tout le site.' ); ?>
            </div>

            <!-- Comportement -->
            <div style="background:#fff;border:1px solid #ddd;border-radius:8px;padding:20px 24px">
                <h2 style="font-size:14px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:#555;margin:0 0 16px">Comportement</h2>
                <?php jimee_popup_field( 'scroll_pct', 'Déclenchement (% de scroll)', $o['scroll_pct'], 'number', '0–100', ['min'=>0,'max'=>100,'suffix'=>'%'] ); ?>
                <?php jimee_popup_field( 'cookie_days', 'Ne plus afficher pendant', $o['cookie_days'], 'number', '', ['min'=>1,'max'=>365,'suffix'=>'jours'] ); ?>
            </div>

            <!-- Aperçu -->
            <div style="background:#f0eeea;border-radius:8px;padding:20px 24px">
                <h2 style="font-size:14px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:#555;margin:0 0 16px">Aperçu</h2>
                <div style="max-width:580px;border-radius:16px;overflow:hidden;display:flex;flex-direction:row;background:#fff;box-shadow:0 8px 32px rgba(0,0,0,.12)">
                    <div id="prev-bg-layer" style="flex:0 0 46%;background-image:url('<?php echo esc_url( $preview_bg ); ?>');background-size:cover;background-position:center;min-height:300px"></div>
                    <div style="flex:1;padding:32px 28px;display:flex;flex-direction:column;justify-content:center;gap:12px;position:relative">
                        <div style="position:absolute;top:12px;right:12px;width:26px;height:26px;border-radius:50%;background:#f0eeea;display:flex;align-items:center;justify-content:center;font-size:11px;color:#888">✕</div>
                        <div id="prev-badge" style="display:inline-block;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:1px;color:#064A2A;background:rgba(6,74,42,.08);padding:5px 14px;border-radius:99px"><?php echo esc_html( $o['badge'] ); ?></div>
                        <div style="font-size:28px;font-weight:800;line-height:1.1;color:#111;letter-spacing:-1px">
                            <span id="prev-title"><?php echo esc_html( $o['title'] ); ?></span>
                            <em id="prev-title-em" style="font-style:italic;color:#064A2A;display:block"> <?php echo esc_html( $o['title_em'] ); ?></em>
                        </div>
                        <div style="font-size:12px;color:#6B7F74;line-height:1.6">
                            <span id="prev-desc"><?php echo esc_html( $o['desc'] ); ?></span>
                            <strong id="prev-code" style="color:#064A2A;font-weight:700;background:rgba(6,74,42,.08);padding:2px 7px;border-radius:5px"> <?php echo esc_html( $o['code'] ); ?></strong>
                        </div>
                        <div id="prev-cta" style="display:inline-block;background:#064A2A;color:#fff;border-radius:99px;padding:11px 24px;font-size:13px;font-weight:600;align-self:flex-start"><?php echo esc_html( $o['cta_text'] ); ?></div>
                        <div id="prev-note" style="font-size:10px;color:#6B7F74;opacity:.7"><?php echo esc_html( $o['note'] ); ?></div>
                    </div>
                </div>
            </div>

        </div>

        <p style="margin-top:24px"><?php submit_button( 'Enregistrer', 'primary', 'submit', false ); ?></p>
    </form>

    <script>
    (function(){
        var map = {
            'jimee_popup_badge':    'prev-badge',
            'jimee_popup_title':    'prev-title',
            'jimee_popup_title_em': 'prev-title-em',
            'jimee_popup_desc':     'prev-desc',
            'jimee_popup_code':     'prev-code',
            'jimee_popup_cta_text': 'prev-cta',
            'jimee_popup_note':     'prev-note',
        };
        Object.keys(map).forEach(function(id){
            var el = document.getElementById(id);
            if (!el) return;
            el.addEventListener('input', function(){
                var prev = document.getElementById(map[id]);
                if (prev) prev.textContent = this.value;
            });
        });
        var frame;
        document.getElementById('jimee-popup-bg-select').addEventListener('click', function(e){
            e.preventDefault();
            if (frame) { frame.open(); return; }
            frame = wp.media({ title: "Choisir l'image de fond", button: { text: 'Utiliser cette image' }, multiple: false });
            frame.on('select', function(){
                var att = frame.state().get('selection').first().toJSON();
                document.getElementById('jimee_popup_bg_img_id').value = att.id;
                document.getElementById('jimee-popup-bg-preview').innerHTML = '<img src="'+att.url+'" style="width:100%;height:100%;object-fit:cover">';
                document.getElementById('jimee-popup-bg-remove').style.display = '';
                var bg = document.getElementById('prev-bg-layer');
                if (bg) bg.style.backgroundImage = 'url('+att.url+')';
            });
            frame.open();
        });
        document.getElementById('jimee-popup-bg-remove').addEventListener('click', function(){
            document.getElementById('jimee_popup_bg_img_id').value = '0';
            document.getElementById('jimee-popup-bg-preview').innerHTML = '<span style="font-size:22px;color:#ccc">🖼</span>';
            this.style.display = 'none';
        });
    })();
    </script>
    <?php
}

/* ── Tab : Bannières ──────────────────────────────────── */
function jimee_site_tab_banners(): void {
    $announcements = jimee_get_announcements();
    $hero_slides   = jimee_get_hero_slides();
    $promo         = jimee_get_promo_banner();
    $double        = jimee_get_double_banners();
    while ( count( $double ) < 2 ) $double[] = [ 'eyebrow' => '', 'title' => '', 'cta' => '', 'link' => '', 'image' => '' ];
    ?>
    <div class="jimee-banners-wrap">
    <form method="post" action="options.php">
        <?php settings_fields( 'jimee_banners' ); ?>

        <!-- ANNONCES -->
        <div class="jimee-admin-card">
            <h2>Barre d'annonces</h2>
            <p class="description">Messages qui défilent en haut du site.</p>
            <div id="jimee-announcements">
                <?php foreach ( $announcements as $msg ) : ?>
                <div class="jimee-ann-row">
                    <input type="text" name="jimee_announcements[]" value="<?php echo esc_attr( $msg ); ?>" class="large-text" placeholder="Message d'annonce...">
                    <button type="button" class="button jimee-remove-row" title="Supprimer">&times;</button>
                </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="button" onclick="jimeeAddAnnouncement()">+ Ajouter un message</button>
        </div>

        <!-- HERO SLIDER -->
        <div class="jimee-admin-card">
            <h2>Hero Slider</h2>
            <p class="description">Les grandes bannières en haut de la page d'accueil.<br><span class="jimee-img-hint">Image : 1440 &times; 720 px minimum, format JPG ou WebP, paysage.</span></p>
            <div id="jimee-hero-slides">
                <?php foreach ( $hero_slides as $i => $slide ) : ?>
                <div class="jimee-slide-card">
                    <div class="jimee-slide-header">
                        <strong>Slide <?php echo $i + 1; ?></strong>
                        <button type="button" class="button-link jimee-remove-slide" style="color:#8B0000">&times; Supprimer</button>
                    </div>
                    <div class="jimee-fields-grid">
                        <div><label>Sur-titre</label><input type="text" name="jimee_hero_slides[<?php echo $i; ?>][eyebrow]" value="<?php echo esc_attr( $slide['eyebrow'] ?? '' ); ?>" class="regular-text"></div>
                        <div><label>Titre <small>(utilisez &lt;em&gt; pour l'italique)</small></label><input type="text" name="jimee_hero_slides[<?php echo $i; ?>][title]" value="<?php echo esc_attr( $slide['title'] ?? '' ); ?>" class="large-text"></div>
                        <div style="grid-column:1/-1"><label>Description</label><textarea name="jimee_hero_slides[<?php echo $i; ?>][desc]" class="large-text" rows="2"><?php echo esc_textarea( $slide['desc'] ?? '' ); ?></textarea></div>
                        <div><label>Texte du bouton</label><input type="text" name="jimee_hero_slides[<?php echo $i; ?>][cta]" value="<?php echo esc_attr( $slide['cta'] ?? '' ); ?>" class="regular-text"></div>
                        <div><label>Lien du bouton</label><input type="text" name="jimee_hero_slides[<?php echo $i; ?>][link]" value="<?php echo esc_attr( $slide['link'] ?? '' ); ?>" class="regular-text" placeholder="/categorie-produit/visage/"></div>
                        <div style="grid-column:1/-1"><label>Image de fond</label>
                            <div class="jimee-image-field">
                                <input type="hidden" name="jimee_hero_slides[<?php echo $i; ?>][image]" value="<?php echo esc_attr( $slide['image'] ?? '' ); ?>" class="jimee-image-id">
                                <?php $img_url = ! empty( $slide['image'] ) ? wp_get_attachment_image_url( $slide['image'], 'medium' ) : ''; ?>
                                <div class="jimee-image-preview" <?php if ( ! $img_url ) echo 'style="display:none"'; ?>>
                                    <img src="<?php echo esc_url( $img_url ); ?>" alt="">
                                    <button type="button" class="jimee-image-remove">&times;</button>
                                </div>
                                <button type="button" class="button jimee-image-upload">Choisir une image</button>
                                <span class="jimee-img-specs">1440 &times; 720 px &middot; JPG / WebP &middot; Paysage</span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="button" onclick="jimeeAddSlide()">+ Ajouter un slide</button>
        </div>

        <!-- PROMO BANNER -->
        <div class="jimee-admin-card">
            <h2>Bannière Promo</h2>
            <p class="description">La bannière "Le Bon Plan Jimee" entre les produits. Pas d'image, uniquement du texte.</p>
            <div class="jimee-fields-grid">
                <div><label>Sur-titre</label><input type="text" name="jimee_promo_banner[eyebrow]" value="<?php echo esc_attr( $promo['eyebrow'] ?? '' ); ?>" class="regular-text"></div>
                <div><label>Titre <small>(utilisez &lt;em&gt; pour l'italique)</small></label><input type="text" name="jimee_promo_banner[title]" value="<?php echo esc_attr( $promo['title'] ?? '' ); ?>" class="large-text"></div>
                <div style="grid-column:1/-1"><label>Description</label><textarea name="jimee_promo_banner[desc]" class="large-text" rows="2"><?php echo esc_textarea( $promo['desc'] ?? '' ); ?></textarea></div>
                <div><label>Texte du bouton</label><input type="text" name="jimee_promo_banner[cta]" value="<?php echo esc_attr( $promo['cta'] ?? '' ); ?>" class="regular-text"></div>
                <div><label>Lien</label><input type="text" name="jimee_promo_banner[link]" value="<?php echo esc_attr( $promo['link'] ?? '' ); ?>" class="regular-text"></div>
            </div>
        </div>

        <!-- DOUBLE BANNER -->
        <div class="jimee-admin-card">
            <h2>Double Bannière</h2>
            <p class="description">Les deux cartes côte à côte (ex: Homme + K-Beauty).<br><span class="jimee-img-hint">Image : 720 &times; 480 px minimum, format JPG ou WebP, paysage.</span></p>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px">
                <?php foreach ( $double as $j => $card ) : ?>
                <div class="jimee-slide-card">
                    <strong>Carte <?php echo $j + 1; ?></strong>
                    <div class="jimee-fields-grid" style="grid-template-columns:1fr">
                        <div><label>Sur-titre</label><input type="text" name="jimee_double_banners[<?php echo $j; ?>][eyebrow]" value="<?php echo esc_attr( $card['eyebrow'] ?? '' ); ?>" class="regular-text"></div>
                        <div><label>Titre</label><input type="text" name="jimee_double_banners[<?php echo $j; ?>][title]" value="<?php echo esc_attr( $card['title'] ?? '' ); ?>" class="regular-text"></div>
                        <div><label>Texte du bouton</label><input type="text" name="jimee_double_banners[<?php echo $j; ?>][cta]" value="<?php echo esc_attr( $card['cta'] ?? '' ); ?>" class="regular-text"></div>
                        <div><label>Lien</label><input type="text" name="jimee_double_banners[<?php echo $j; ?>][link]" value="<?php echo esc_attr( $card['link'] ?? '' ); ?>" class="regular-text"></div>
                        <div><label>Image de fond</label>
                            <div class="jimee-image-field">
                                <input type="hidden" name="jimee_double_banners[<?php echo $j; ?>][image]" value="<?php echo esc_attr( $card['image'] ?? '' ); ?>" class="jimee-image-id">
                                <?php $dimg = ! empty( $card['image'] ) ? wp_get_attachment_image_url( $card['image'], 'medium' ) : ''; ?>
                                <div class="jimee-image-preview" <?php if ( ! $dimg ) echo 'style="display:none"'; ?>>
                                    <img src="<?php echo esc_url( $dimg ); ?>" alt="">
                                    <button type="button" class="jimee-image-remove">&times;</button>
                                </div>
                                <button type="button" class="button jimee-image-upload">Choisir une image</button>
                                <span class="jimee-img-specs">720 &times; 480 px &middot; JPG / WebP &middot; Paysage</span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <?php submit_button( 'Enregistrer les bannières' ); ?>
    </form>
    </div>

    <style>
    .jimee-banners-wrap { max-width: 960px; }
    .jimee-admin-card { background: #fff; border: 1px solid #ddd; border-radius: 12px; padding: 24px 28px; margin-bottom: 20px; }
    .jimee-admin-card h2 { font-size: 18px; font-weight: 600; margin: 0 0 4px; padding: 0; }
    .jimee-admin-card .description { margin-bottom: 16px; }
    .jimee-fields-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px 16px; margin-top: 12px; }
    .jimee-fields-grid label { display: block; font-size: 12px; font-weight: 600; margin-bottom: 4px; }
    .jimee-fields-grid small { font-weight: 400; color: #999; }
    .jimee-slide-card { background: #f9f9f9; border: 1px solid #e8e4df; border-radius: 8px; padding: 16px 20px; margin-bottom: 12px; }
    .jimee-slide-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
    .jimee-ann-row { display: flex; gap: 8px; margin-bottom: 8px; }
    .jimee-ann-row input { flex: 1; }
    .jimee-remove-row { width: 36px; height: 36px; font-size: 18px; color: #999; border-color: #ddd; display: flex; align-items: center; justify-content: center; padding: 0; }
    .jimee-remove-row:hover { color: #8B0000; border-color: #8B0000; }
    .jimee-image-field { display: flex; align-items: center; gap: 12px; }
    .jimee-image-preview { position: relative; width: 120px; height: 70px; border-radius: 8px; overflow: hidden; }
    .jimee-image-preview img { width: 100%; height: 100%; object-fit: cover; }
    .jimee-image-remove { position: absolute; top: 4px; right: 4px; width: 22px; height: 22px; border-radius: 50%; background: rgba(0,0,0,.6); color: #fff; border: none; cursor: pointer; font-size: 14px; display: flex; align-items: center; justify-content: center; }
    .jimee-img-specs { display: block; font-size: 11px; color: #999; margin-top: 6px; }
    .jimee-img-hint { display: inline-block; font-size: 12px; color: #999; margin-top: 4px; background: #f9f9f9; padding: 4px 10px; border-radius: 4px; border: 1px dashed #ddd; }
    </style>

    <script>
    jQuery(function($){
        $(document).on('click', '.jimee-image-upload', function(e){
            e.preventDefault();
            var field = $(this).closest('.jimee-image-field');
            var frame = wp.media({ title: 'Choisir une image', multiple: false, library: { type: 'image' } });
            frame.on('select', function(){
                var att = frame.state().get('selection').first().toJSON();
                field.find('.jimee-image-id').val(att.id);
                var url = att.sizes && att.sizes.medium ? att.sizes.medium.url : att.url;
                field.find('.jimee-image-preview img').attr('src', url);
                field.find('.jimee-image-preview').show();
            });
            frame.open();
        });
        $(document).on('click', '.jimee-image-remove', function(e){
            e.preventDefault();
            var field = $(this).closest('.jimee-image-field');
            field.find('.jimee-image-id').val('');
            field.find('.jimee-image-preview').hide();
        });
        $(document).on('click', '.jimee-remove-row', function(){ $(this).closest('.jimee-ann-row').remove(); });
        $(document).on('click', '.jimee-remove-slide', function(){ $(this).closest('.jimee-slide-card').remove(); });
    });

    function jimeeAddAnnouncement(){
        var html = '<div class="jimee-ann-row">'
            + '<input type="text" name="jimee_announcements[]" value="" class="large-text" placeholder="Message d\'annonce...">'
            + '<button type="button" class="button jimee-remove-row" title="Supprimer">&times;</button></div>';
        document.getElementById('jimee-announcements').insertAdjacentHTML('beforeend', html);
    }

    function jimeeAddSlide(){
        var container = document.getElementById('jimee-hero-slides');
        var idx = container.querySelectorAll('.jimee-slide-card').length;
        var html = '<div class="jimee-slide-card">'
            + '<div class="jimee-slide-header"><strong>Slide '+(idx+1)+'</strong>'
            + '<button type="button" class="button-link jimee-remove-slide" style="color:#8B0000">&times; Supprimer</button></div>'
            + '<div class="jimee-fields-grid">'
            + '<div><label>Sur-titre</label><input type="text" name="jimee_hero_slides['+idx+'][eyebrow]" class="regular-text"></div>'
            + '<div><label>Titre</label><input type="text" name="jimee_hero_slides['+idx+'][title]" class="large-text"></div>'
            + '<div style="grid-column:1/-1"><label>Description</label><textarea name="jimee_hero_slides['+idx+'][desc]" class="large-text" rows="2"></textarea></div>'
            + '<div><label>Texte du bouton</label><input type="text" name="jimee_hero_slides['+idx+'][cta]" class="regular-text"></div>'
            + '<div><label>Lien du bouton</label><input type="text" name="jimee_hero_slides['+idx+'][link]" class="regular-text" placeholder="/categorie-produit/visage/"></div>'
            + '<div style="grid-column:1/-1"><label>Image de fond</label>'
            + '<div class="jimee-image-field"><input type="hidden" name="jimee_hero_slides['+idx+'][image]" value="" class="jimee-image-id">'
            + '<div class="jimee-image-preview" style="display:none"><img src="" alt=""><button type="button" class="jimee-image-remove">&times;</button></div>'
            + '<button type="button" class="button jimee-image-upload">Choisir une image</button></div></div>'
            + '</div></div>';
        container.insertAdjacentHTML('beforeend', html);
    }
    </script>
    <?php
}

/* ── Tab : Coming soon ────────────────────────────────── */
function jimee_site_tab_cs(): void {
    $enabled  = get_option( 'jimee_cs_enabled', '0' );
    $password = get_option( 'jimee_cs_password', 'FoufouAli2026' );
    ?>
    <form method="post" action="options.php" style="max-width:600px">
        <?php settings_fields( 'jimee_coming_soon' ); ?>

        <div style="background:#fff;border:1px solid #ddd;border-radius:12px;padding:28px;margin-bottom:20px">

            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
                <div>
                    <strong style="font-size:16px">Mode Coming Soon</strong>
                    <p class="description" style="margin:4px 0 0">Quand activé, les visiteurs voient une page de mot de passe.</p>
                </div>
                <label class="jimee-toggle">
                    <input type="hidden" name="jimee_cs_enabled" value="0">
                    <input type="checkbox" name="jimee_cs_enabled" value="1" <?php checked( $enabled, '1' ); ?>>
                    <span class="jimee-toggle-track"><span class="jimee-toggle-thumb"></span></span>
                </label>
            </div>

            <div style="margin-bottom:12px">
                <label style="display:block;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px">Mot de passe</label>
                <input type="text" name="jimee_cs_password" value="<?php echo esc_attr( $password ); ?>"
                       class="regular-text" placeholder="FoufouAli2026"
                       style="width:100%;padding:10px 14px;border:1.5px solid #ddd;border-radius:8px">
                <p class="description" style="margin-top:6px">Les visiteurs doivent entrer ce mot de passe pour accéder au site.</p>
            </div>

            <div style="padding:12px 16px;background:#f8f6f3;border-radius:8px;font-size:13px;color:#555">
                Les utilisateurs connectés (admin, technique, gérant) voient toujours le site normalement.
            </div>
        </div>

        <?php submit_button( 'Enregistrer' ); ?>
    </form>
    <style>
    .jimee-toggle { position:relative; display:inline-block; width:52px; height:28px; cursor:pointer; }
    .jimee-toggle input[type="checkbox"] { opacity:0; width:0; height:0; position:absolute; }
    .jimee-toggle-track { position:absolute; inset:0; background:#ccc; border-radius:28px; transition:all .3s; }
    .jimee-toggle input:checked + .jimee-toggle-track { background:#064A2A; }
    .jimee-toggle-thumb { position:absolute; top:3px; left:3px; width:22px; height:22px; background:#fff; border-radius:50%; transition:all .3s; box-shadow:0 1px 3px rgba(0,0,0,.2); }
    .jimee-toggle input:checked + .jimee-toggle-track .jimee-toggle-thumb { left:27px; }
    </style>
    <?php
}

/* ── Tab : Formulaire ─────────────────────────────────── */
function jimee_site_tab_formulaire(): void {
    $email = get_option( 'jimee_contact_email', get_option( 'admin_email' ) );
    ?>
    <form method="post" action="options.php" style="max-width:600px">
        <?php settings_fields( 'jimee_formulaire' ); ?>

        <div style="background:#fff;border:1px solid #ddd;border-radius:12px;padding:28px;margin-bottom:20px">
            <h2 style="font-size:16px;font-weight:600;margin:0 0 8px">Email de réception des formulaires</h2>
            <p class="description" style="margin:0 0 16px">Cet email recevra toutes les soumissions du formulaire de contact.</p>
            <label style="display:block;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px">Adresse email</label>
            <input type="email" name="jimee_contact_email"
                   value="<?php echo esc_attr( $email ); ?>"
                   class="regular-text"
                   placeholder="contact@exemple.dz"
                   style="width:100%;max-width:400px;padding:10px 14px;border:1.5px solid #ddd;border-radius:8px;font-size:14px">
            <p class="description" style="margin-top:8px">Si vide, les emails seront envoyés à l'adresse administrateur WordPress par défaut.</p>
        </div>

        <div style="background:#fff;border:1px solid #ddd;border-radius:12px;padding:28px;margin-bottom:20px">
            <h2 style="font-size:16px;font-weight:600;margin:0 0 8px">Formulaires reliés à cet email</h2>
            <p class="description" style="margin:0 0 16px">Les formulaires suivants utilisent l'adresse email configurée ci-dessus pour leurs notifications.</p>
            <ul style="margin:0;padding:0;list-style:none">
                <li style="display:flex;align-items:flex-start;gap:10px;padding:12px 0;border-bottom:1px solid #f0eeea">
                    <span style="width:8px;height:8px;border-radius:50%;background:#71ac1e;flex-shrink:0;margin-top:5px"></span>
                    <div>
                        <strong>Formulaire de contact</strong>
                        <code style="font-size:11px;color:#888;margin-left:8px;background:#f5f5f5;padding:1px 5px;border-radius:3px">page-contact.php</code>
                        <p style="margin:4px 0 0;font-size:12px;color:#888">Envoie un email à chaque soumission depuis la page Contact.</p>
                    </div>
                </li>
                <li style="display:flex;align-items:flex-start;gap:10px;padding:12px 0">
                    <span style="width:8px;height:8px;border-radius:50%;background:#71ac1e;flex-shrink:0;margin-top:5px"></span>
                    <div>
                        <strong>Formulaire Newsletter</strong>
                        <code style="font-size:11px;color:#888;margin-left:8px;background:#f5f5f5;padding:1px 5px;border-radius:3px">inc/woocommerce.php</code>
                        <p style="margin:4px 0 0;font-size:12px;color:#888">Envoie un email à chaque nouvelle inscription depuis le bloc newsletter (page d'accueil).</p>
                    </div>
                </li>
            </ul>
        </div>

        <?php submit_button( 'Enregistrer' ); ?>
    </form>
    <?php
}
