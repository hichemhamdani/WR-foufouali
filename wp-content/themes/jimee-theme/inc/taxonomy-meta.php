<?php
/**
 * Custom image meta field for product_cat taxonomy.
 * Meta key: jimee_cat_image_id (attachment ID)
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ── Enqueue media uploader on taxonomy pages ─────────── */
add_action( 'admin_enqueue_scripts', function( $hook ) {
    if ( ! in_array( $hook, [ 'edit-tags.php', 'term.php' ], true ) ) return;
    if ( ( $_GET['taxonomy'] ?? '' ) !== 'product_cat' ) return;

    wp_enqueue_media();
    wp_add_inline_script( 'jquery-core', jimee_cat_image_js() );
} );

/* ── Add field — new category form ───────────────────── */
add_action( 'product_cat_add_form_fields', function() {
    ?>
    <div class="form-field">
        <label><?php esc_html_e( 'Image d\'archive', 'jimee' ); ?></label>
        <?php jimee_cat_image_field( 0 ); ?>
        <p class="description">Image affichée dans le hero de la page archive de cette catégorie.</p>
    </div>
    <?php
} );

/* ── Edit field — edit category form ─────────────────── */
add_action( 'product_cat_edit_form_fields', function( WP_Term $term ) {
    ?>
    <tr class="form-field">
        <th scope="row"><label><?php esc_html_e( 'Image d\'archive', 'jimee' ); ?></label></th>
        <td>
            <?php jimee_cat_image_field( (int) get_term_meta( $term->term_id, 'jimee_cat_image_id', true ) ); ?>
            <p class="description">Image affichée dans le hero de la page archive de cette catégorie.</p>
        </td>
    </tr>
    <?php
} );

/* ── Save on create ───────────────────────────────────── */
add_action( 'created_product_cat', 'jimee_save_cat_image_meta' );

/* ── Save on update ───────────────────────────────────── */
add_action( 'edited_product_cat', 'jimee_save_cat_image_meta' );

function jimee_save_cat_image_meta( int $term_id ): void {
    if ( ! isset( $_POST['jimee_cat_image_nonce'] ) ||
         ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['jimee_cat_image_nonce'] ) ), 'jimee_cat_image_' . $term_id ) ) {
        return;
    }

    $image_id = isset( $_POST['jimee_cat_image_id'] ) ? absint( $_POST['jimee_cat_image_id'] ) : 0;

    if ( $image_id ) {
        update_term_meta( $term_id, 'jimee_cat_image_id', $image_id );
    } else {
        delete_term_meta( $term_id, 'jimee_cat_image_id' );
    }
}

/* ── HTML helper ──────────────────────────────────────── */
function jimee_cat_image_field( int $image_id ): void {
    $term_id  = isset( $_GET['tag_ID'] ) ? absint( $_GET['tag_ID'] ) : 0;
    $img_src  = $image_id ? wp_get_attachment_image_url( $image_id, 'medium' ) : '';
    $nonce    = wp_create_nonce( 'jimee_cat_image_' . $term_id );
    ?>
    <div class="jimee-cat-image-wrap" style="max-width:320px">
        <wp:nonce style="display:none"></wp:nonce>
        <input type="hidden" name="jimee_cat_image_id" id="jimee_cat_image_id" value="<?php echo esc_attr( $image_id ?: '' ); ?>">
        <input type="hidden" name="jimee_cat_image_nonce" value="<?php echo esc_attr( $nonce ); ?>">

        <div id="jimee-cat-image-preview" style="margin-bottom:8px;<?php echo $img_src ? '' : 'display:none'; ?>">
            <img id="jimee-cat-image-img" src="<?php echo esc_url( $img_src ); ?>"
                 style="max-width:100%;height:160px;object-fit:cover;border-radius:6px;border:1px solid #ddd;">
        </div>

        <button type="button" id="jimee-cat-image-btn" class="button">
            <?php echo $image_id ? esc_html__( 'Changer l\'image', 'jimee' ) : esc_html__( 'Choisir une image', 'jimee' ); ?>
        </button>
        <?php if ( $image_id ) : ?>
        <button type="button" id="jimee-cat-image-remove" class="button" style="margin-left:6px;color:#b32d2e">
            <?php esc_html_e( 'Supprimer', 'jimee' ); ?>
        </button>
        <?php endif; ?>
    </div>
    <?php
}

/* ── Inline JS ────────────────────────────────────────── */
function jimee_cat_image_js(): string {
    return <<<'JS'
jQuery(function($){
    var frame;

    $(document).on('click', '#jimee-cat-image-btn', function(e){
        e.preventDefault();
        if (frame) { frame.open(); return; }
        frame = wp.media({
            title: 'Choisir une image d\'archive',
            button: { text: 'Utiliser cette image' },
            multiple: false,
            library: { type: 'image' }
        });
        frame.on('select', function(){
            var att = frame.state().get('selection').first().toJSON();
            $('#jimee_cat_image_id').val(att.id);
            var src = (att.sizes && att.sizes.medium) ? att.sizes.medium.url : att.url;
            $('#jimee-cat-image-img').attr('src', src);
            $('#jimee-cat-image-preview').show();
            $('#jimee-cat-image-btn').text('Changer l\'image');
            if (!$('#jimee-cat-image-remove').length) {
                $('#jimee-cat-image-btn').after('<button type="button" id="jimee-cat-image-remove" class="button" style="margin-left:6px;color:#b32d2e">Supprimer</button>');
            }
        });
        frame.open();
    });

    $(document).on('click', '#jimee-cat-image-remove', function(e){
        e.preventDefault();
        $('#jimee_cat_image_id').val('');
        $('#jimee-cat-image-preview').hide();
        $('#jimee-cat-image-img').attr('src', '');
        $('#jimee-cat-image-btn').text('Choisir une image');
        $(this).remove();
    });
});
JS;
}
