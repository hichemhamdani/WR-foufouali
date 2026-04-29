<?php
/**
 * Popup promo — admin settings page.
 * Options stored under 'jimee_popup' (single serialized array).
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ── Register settings ────────────────────────────────── */
add_action( 'admin_init', function() {
    register_setting( 'jimee_popup_group', 'jimee_popup', [
        'sanitize_callback' => 'jimee_popup_sanitize',
        'default'           => jimee_popup_defaults(),
    ] );
} );

function jimee_popup_defaults(): array {
    return [
        'enabled'    => 1,
        'bg_img_id'  => 0,
        'badge'      => 'Offre limitée',
        'title'      => '-15%',
        'title_em'   => 'sur votre commande',
        'desc'       => 'Utilisez votre code promo à la caisse :',
        'code'       => 'FOUFOU15',
        'cta_text'   => 'Voir la boutique',
        'cta_url'    => '',
        'note'       => 'Valable une seule fois, sur tout le site.',
        'scroll_pct' => 25,
        'cookie_days'=> 30,
    ];
}

function jimee_popup_sanitize( $raw ): array {
    $d = jimee_popup_defaults();
    return [
        'enabled'     => ! empty( $raw['enabled'] ) ? 1 : 0,
        'bg_img_id'   => (int) ( $raw['bg_img_id'] ?? 0 ),
        'badge'       => sanitize_text_field( $raw['badge']      ?? $d['badge'] ),
        'title'       => sanitize_text_field( $raw['title']      ?? $d['title'] ),
        'title_em'    => sanitize_text_field( $raw['title_em']   ?? $d['title_em'] ),
        'desc'        => sanitize_textarea_field( $raw['desc']   ?? $d['desc'] ),
        'code'        => strtoupper( sanitize_text_field( $raw['code'] ?? $d['code'] ) ),
        'cta_text'    => sanitize_text_field( $raw['cta_text']   ?? $d['cta_text'] ),
        'cta_url'     => esc_url_raw( $raw['cta_url'] ?? $d['cta_url'] ),
        'note'        => sanitize_text_field( $raw['note']       ?? $d['note'] ),
        'scroll_pct'  => min( 100, max( 0, (int) ( $raw['scroll_pct']  ?? $d['scroll_pct'] ) ) ),
        'cookie_days' => min( 365, max( 1,  (int) ( $raw['cookie_days'] ?? $d['cookie_days'] ) ) ),
    ];
}

/* ── Helper: get options with defaults ────────────────── */
function jimee_popup_options(): array {
    return wp_parse_args( (array) get_option( 'jimee_popup', [] ), jimee_popup_defaults() );
}

/* ── Admin menu ───────────────────────────────────────── */
add_action( 'admin_menu', function() {
    add_menu_page(
        'Popup Promo',
        'Popup Promo',
        'manage_options',
        'jimee-popup',
        'jimee_popup_settings_page',
        'dashicons-megaphone',
        59
    );
} );

/* ── Enqueue media uploader on our page ───────────────── */
add_action( 'admin_enqueue_scripts', function( $hook ) {
    if ( $hook !== 'toplevel_page_jimee-popup' ) return;
    wp_enqueue_media();
} );

/* ── Settings page HTML ───────────────────────────────── */
function jimee_popup_settings_page(): void {
    $o       = jimee_popup_options();
    $bg_url  = $o['bg_img_id'] ? wp_get_attachment_image_url( $o['bg_img_id'], 'large' ) : '';
    $default_bg = get_template_directory_uri() . '/assets/img/masque-visage-routine-skincare.jpg';
    $preview_bg = $bg_url ?: $default_bg;
    ?>
    <div class="wrap">
        <h1 style="display:flex;align-items:center;gap:10px">
            <span style="font-size:22px">🎁</span> Popup Promo
        </h1>
        <p style="color:#666;margin-top:4px">Configure la popup de bienvenue affichée au scroll sur le site.</p>

        <?php if ( isset( $_GET['settings-updated'] ) ) : ?>
            <div class="notice notice-success is-dismissible"><p>Paramètres sauvegardés.</p></div>
        <?php endif; ?>

        <form method="post" action="options.php" style="margin-top:24px">
            <?php settings_fields( 'jimee_popup_group' ); ?>

            <div style="max-width:720px;display:flex;flex-direction:column;gap:24px">

                <!-- Active / Inactive -->
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
                            <?php if ( $bg_url ) : ?>
                                <button type="button" id="jimee-popup-bg-remove" class="button button-link-delete">Supprimer</button>
                            <?php else : ?>
                                <button type="button" id="jimee-popup-bg-remove" class="button button-link-delete" style="display:none">Supprimer</button>
                            <?php endif; ?>
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
                        <!-- colonne image -->
                        <div id="prev-bg-layer" style="flex:0 0 46%;background-image:url('<?php echo esc_url( $preview_bg ); ?>');background-size:cover;background-position:center;min-height:300px"></div>
                        <!-- colonne contenu -->
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

            <p style="margin-top:24px">
                <?php submit_button( 'Enregistrer', 'primary', 'submit', false ); ?>
            </p>
        </form>
    </div>

    <script>
    (function(){
        /* Live text preview */
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

        /* Media uploader */
        var frame;
        document.getElementById('jimee-popup-bg-select').addEventListener('click', function(e){
            e.preventDefault();
            if (frame) { frame.open(); return; }
            frame = wp.media({ title: 'Choisir l\'image de fond', button: { text: 'Utiliser cette image' }, multiple: false });
            frame.on('select', function(){
                var att = frame.state().get('selection').first().toJSON();
                document.getElementById('jimee_popup_bg_img_id').value = att.id;
                var preview = document.getElementById('jimee-popup-bg-preview');
                preview.innerHTML = '<img src="' + att.url + '" style="width:100%;height:100%;object-fit:cover">';
                document.getElementById('jimee-popup-bg-remove').style.display = '';
                var bgLayer = document.getElementById('prev-bg-layer');
                if (bgLayer) bgLayer.style.backgroundImage = 'url(' + att.url + ')';
            });
            frame.open();
        });

        document.getElementById('jimee-popup-bg-remove').addEventListener('click', function(){
            document.getElementById('jimee_popup_bg_img_id').value = '0';
            var preview = document.getElementById('jimee-popup-bg-preview');
            preview.innerHTML = '<span style="font-size:22px;color:#ccc">🖼</span>';
            this.style.display = 'none';
        });
    })();
    </script>
    <?php
}

/* ── Field helper ─────────────────────────────────────── */
function jimee_popup_field( string $key, string $label, $value, string $type = 'text', string $placeholder = '', array $extra = [] ): void {
    $id   = 'jimee_popup_' . $key;
    $name = 'jimee_popup[' . $key . ']';
    echo '<div style="margin-bottom:16px">';
    echo '<label for="' . esc_attr( $id ) . '" style="display:block;font-size:13px;font-weight:600;margin-bottom:6px">' . esc_html( $label ) . '</label>';
    if ( $type === 'textarea' ) {
        echo '<textarea id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" rows="3" placeholder="' . esc_attr( $placeholder ) . '" style="width:100%;border:1px solid #ddd;border-radius:6px;padding:8px 12px;font-size:13px">' . esc_textarea( $value ) . '</textarea>';
    } else {
        $attrs = 'type="' . esc_attr( $type ) . '" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '" placeholder="' . esc_attr( $placeholder ) . '" style="border:1px solid #ddd;border-radius:6px;padding:8px 12px;font-size:13px;width:' . ( $type === 'number' ? '100px' : '100%' ) . '"';
        if ( isset( $extra['min'] ) ) $attrs .= ' min="' . (int) $extra['min'] . '"';
        if ( isset( $extra['max'] ) ) $attrs .= ' max="' . (int) $extra['max'] . '"';
        echo '<div style="display:flex;align-items:center;gap:8px"><input ' . $attrs . '>';
        if ( ! empty( $extra['suffix'] ) ) echo '<span style="font-size:13px;color:#888">' . esc_html( $extra['suffix'] ) . '</span>';
        echo '</div>';
    }
    echo '</div>';
}
