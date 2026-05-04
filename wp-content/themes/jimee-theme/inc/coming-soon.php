<?php
/**
 * Coming Soon — toggle via admin menu.
 * Accessible à : Administrator + Technique
 * Le Gérant ne voit PAS ce menu.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ============================================================
   ADMIN PAGE
   ============================================================ */

/* Admin menu handled by admin-site-settings.php */

add_action( 'admin_init', 'jimee_cs_register' );
function jimee_cs_register() {
    register_setting( 'jimee_coming_soon', 'jimee_cs_enabled' );
    register_setting( 'jimee_coming_soon', 'jimee_cs_password' );
}

function jimee_cs_page() {
    $enabled  = get_option( 'jimee_cs_enabled', '0' );
    $password = get_option( 'jimee_cs_password', 'FoufouAli2026' );
    ?>
    <div class="wrap" style="max-width:600px">
        <h1 style="font-size:28px;font-weight:300;margin-bottom:24px">Coming <strong>Soon</strong></h1>

        <?php settings_errors(); ?>

        <form method="post" action="options.php">
            <?php settings_fields( 'jimee_coming_soon' ); ?>

            <div style="background:#fff;border:1px solid #ddd;border-radius:12px;padding:28px;margin-bottom:20px">

                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
                    <div>
                        <strong style="font-size:16px">Mode Coming Soon</strong>
                        <p class="description" style="margin:4px 0 0">Quand active, les visiteurs voient une page de mot de passe.</p>
                    </div>
                    <label class="jimee-toggle">
                        <input type="hidden" name="jimee_cs_enabled" value="0">
                        <input type="checkbox" name="jimee_cs_enabled" value="1" <?php checked( $enabled, '1' ); ?>>
                        <span class="jimee-toggle-track"><span class="jimee-toggle-thumb"></span></span>
                    </label>
                    <style>
                    .jimee-toggle { position:relative; display:inline-block; width:52px; height:28px; cursor:pointer; }
                    .jimee-toggle input[type="checkbox"] { opacity:0; width:0; height:0; position:absolute; }
                    .jimee-toggle-track { position:absolute; inset:0; background:#ccc; border-radius:28px; transition:all .3s; }
                    .jimee-toggle input:checked + .jimee-toggle-track { background:#064A2A; }
                    .jimee-toggle-thumb { position:absolute; top:3px; left:3px; width:22px; height:22px; background:#fff; border-radius:50%; transition:all .3s; box-shadow:0 1px 3px rgba(0,0,0,.2); }
                    .jimee-toggle input:checked + .jimee-toggle-track .jimee-toggle-thumb { left:27px; }
                    </style>
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
    </div>
    <?php
}

/* ============================================================
   FRONT — Block access if enabled
   ============================================================ */

// Disable SG cache when coming soon is active
add_action( 'init', 'jimee_cs_nocache', 1 );
function jimee_cs_nocache() {
    if ( get_option( 'jimee_cs_enabled' ) !== '1' ) return;
    if ( is_user_logged_in() ) return;
    if ( is_admin() || wp_doing_ajax() || defined( 'REST_REQUEST' ) ) return;

    $password = get_option( 'jimee_cs_password', 'FoufouAli2026' );
    if ( isset( $_COOKIE['jimee_preview'] ) && $_COOKIE['jimee_preview'] === md5( $password ) ) return;

    // Tell SiteGround and browsers not to cache
    nocache_headers();
    header( 'X-Cache-Enabled: False' );
    if ( ! defined( 'DONOTCACHEPAGE' ) ) define( 'DONOTCACHEPAGE', true );
}

add_action( 'template_redirect', 'jimee_cs_redirect' );
function jimee_cs_redirect() {
    if ( get_option( 'jimee_cs_enabled' ) !== '1' ) return;
    if ( is_user_logged_in() ) return;
    if ( is_admin() ) return;

    // Allow AJAX, REST, cron
    if ( wp_doing_ajax() || defined( 'REST_REQUEST' ) || wp_doing_cron() ) return;

    // Allow wp-login
    if ( strpos( $_SERVER['REQUEST_URI'], 'wp-login' ) !== false ) return;

    // Check cookie
    $password = get_option( 'jimee_cs_password', 'FoufouAli2026' );
    if ( isset( $_COOKIE['jimee_preview'] ) && $_COOKIE['jimee_preview'] === md5( $password ) ) return;

    // Handle form submission
    if ( isset( $_POST['preview_pass'] ) && $_POST['preview_pass'] === $password ) {
        nocache_headers();
        setcookie( 'jimee_preview', md5( $password ), time() + 30 * DAY_IN_SECONDS, '/' );
        wp_safe_redirect( home_url( '/' ) );
        exit;
    }

    // Show coming soon page
    nocache_headers();
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex, nofollow">
        <title>Foufou Ali — Bientôt disponible</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,900;1,600&display=swap" rel="stylesheet">
        <style>
            * { margin:0; padding:0; box-sizing:border-box; }
            body { font-family:'Poppins',sans-serif; background:#F8F6F3; min-height:100vh; display:flex; align-items:center; justify-content:center; }
            .cs { text-align:center; padding:40px 24px; max-width:440px; width:100%; }
            .cs-logo { font-size:32px; font-weight:900; letter-spacing:-1px; color:#064A2A; margin-bottom:40px; display:block; line-height:1; }
            .cs-logo em { font-style:italic; color:#71ac1e; }
            .cs-badge { display:inline-block; font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:1.5px; color:#064A2A; background:rgba(6,74,42,.08); padding:5px 16px; border-radius:99px; margin-bottom:20px; }
            .cs h1 { font-size:32px; font-weight:300; margin-bottom:12px; color:#111; line-height:1.2; }
            .cs h1 em { font-style:italic; font-weight:700; color:#064A2A; }
            .cs p { font-size:14px; color:#6B7F74; margin-bottom:36px; line-height:1.7; }
            .cs-form { display:flex; gap:8px; max-width:340px; margin:0 auto; }
            .cs-input { flex:1; padding:14px 20px; border:1.5px solid #E8E4DF; border-radius:999px; font-size:14px; font-family:inherit; outline:none; background:#fff; transition:border-color .2s,box-shadow .2s; color:#111; }
            .cs-input:focus { border-color:#064A2A; box-shadow:0 0 0 3px rgba(6,74,42,.08); }
            .cs-btn { padding:14px 28px; background:#064A2A; color:#fff; border:none; border-radius:999px; font-size:14px; font-weight:600; font-family:inherit; cursor:pointer; transition:background .2s,transform .15s; white-space:nowrap; }
            .cs-btn:hover { background:#71ac1e; transform:translateY(-1px); }
            .cs-btn:active { transform:translateY(0); }
            .cs-divider { width:40px; height:2px; background:rgba(6,74,42,.15); border-radius:2px; margin:36px auto; }
            .cs-reassurance { display:flex; gap:20px; justify-content:center; flex-wrap:wrap; margin-bottom:0; }
            .cs-reassurance span { font-size:12px; color:#6B7F74; display:flex; align-items:center; gap:6px; }
            .cs-reassurance svg { flex-shrink:0; color:#71ac1e; }
            .cs-footer { margin-top:40px; font-size:11px; color:#bbb; }
            @media(max-width:400px) { .cs-form { flex-direction:column; } .cs-btn { padding:14px; } }
        </style>
    </head>
    <body>
        <div class="cs">
            <span class="cs-logo">Foufou <em>Ali</em></span>
            <span class="cs-badge">Bientôt disponible</span>
            <h1>Notre boutique<br><em>arrive bientôt</em></h1>
            <p>Nous préparons quelque chose de beau pour vous.<br>Entrez le mot de passe pour accéder à l'aperçu.</p>
            <form method="post" class="cs-form">
                <input type="password" name="preview_pass" placeholder="Mot de passe" class="cs-input" autocomplete="off" required>
                <button type="submit" class="cs-btn">Accéder</button>
            </form>
            <div class="cs-divider"></div>
            <div class="cs-reassurance">
                <span>
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    Produits 100% authentiques
                </span>
                <span>
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    Livraison partout en Algérie
                </span>
            </div>
            <div class="cs-footer">&copy; <?php echo date('Y'); ?> Foufou Ali &mdash; Alger, Algérie</div>
        </div>
    </body>
    </html>
    <?php
    exit;
}
