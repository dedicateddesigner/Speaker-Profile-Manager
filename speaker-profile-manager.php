<?php

/**
 * Plugin Name: Speaker Profile Manager
 * Description: Manage speaker profiles with Elementor Loop Grid compatibility.
 * Version: 1.0.0
 * Author: Dedicated Designer
 * Text Domain: speaker-profile-manager
 */

if (!defined('ABSPATH')) {
    exit;
}

define('SPM_VERSION', '1.0.0');
define('SPM_PATH', plugin_dir_path(__FILE__));
define('SPM_URL', plugin_dir_url(__FILE__));

/**
 * Register Speaker Custom Post Type
 */
function spm_register_speaker_cpt()
{

    $labels = array(
        'name'               => 'Speakers',
        'singular_name'      => 'Speaker',
        'menu_name'          => 'Speakers',
        'add_new'            => 'Add Speaker',
        'add_new_item'       => 'Add New Speaker',
        'edit_item'          => 'Edit Speaker',
        'new_item'           => 'New Speaker',
        'view_item'          => 'View Speaker',
        'search_items'       => 'Search Speakers',
        'not_found'          => 'No speakers found',
        'not_found_in_trash' => 'No speakers found in Trash',
    );

    register_post_type('spm_speaker', array(
        'labels'             => $labels,
        'public'             => true,
        'show_in_rest'       => true,
        'menu_icon'          => 'dashicons-groups',
        'menu_position'      => 20,
        'supports'           => array(
            'title',
            'thumbnail',
            'revisions',
        ),
        'has_archive'        => true,
        'rewrite'            => array(
            'slug' => 'speakers',
            'with_front' => false,
        ),
        'publicly_queryable' => true,
        'exclude_from_search' => false,
    ));
}
add_action('init', 'spm_register_speaker_cpt');


/**
 * Register custom fields
 */
function spm_register_meta_fields()
{

    $fields = array(
        '_speaker_name' => 'string',
        '_speaker_title' => 'string',
        '_speaker_company' => 'string',
        '_speaker_company_logo' => 'integer',
        '_speaker_ring_color' => 'string',
    );

    foreach ($fields as $key => $type) {

        register_post_meta('spm_speaker', $key, array(
            'type'              => $type,
            'single'            => true,
            'show_in_rest'      => true,
            'sanitize_callback' => 'spm_sanitize_meta',
            'auth_callback'     => function () {
                return current_user_can('edit_posts');
            },
        ));
    }
}
add_action('init', 'spm_register_meta_fields');


/**
 * Sanitize meta values
 */
function spm_sanitize_meta($value)
{

    if (is_array($value) || is_object($value)) {
        return '';
    }

    return sanitize_text_field($value);
}


/**
 * Add Speaker Details Meta Box
 */
function spm_add_meta_box()
{

    add_meta_box(
        'spm_speaker_details',
        'Speaker Information',
        'spm_render_meta_box',
        'spm_speaker',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'spm_add_meta_box');


/**
 * Render Meta Box
 */
function spm_render_meta_box($post)
{

    wp_nonce_field('spm_save_speaker', 'spm_speaker_nonce');

    $speaker_name = get_post_meta($post->ID, '_speaker_name', true);
    $speaker_title = get_post_meta($post->ID, '_speaker_title', true);
    $company = get_post_meta($post->ID, '_speaker_company', true);
    $company_logo = get_post_meta($post->ID, '_speaker_company_logo', true);
    $ring_color = get_post_meta($post->ID, '_speaker_ring_color', true);

    if (!$ring_color) {
        $ring_color = '#A4D600';
    }

    $logo_url = $company_logo
        ? wp_get_attachment_image_url($company_logo, 'medium')
        : '';
?>

    <style>
        .spm-field {
            margin-bottom: 22px;
        }

        .spm-field label {
            display: block;
            font-weight: 600;
            margin-bottom: 7px;
        }

        .spm-field input[type="text"],
        .spm-field textarea {
            width: 100%;
            max-width: 650px;
        }

        .spm-logo-preview {
            display: block;
            max-width: 160px;
            max-height: 100px;
            margin: 10px 0;
            object-fit: contain;
        }

        .spm-help {
            color: #646970;
            font-size: 12px;
        }
    </style>

    <div class="spm-field">
        <label for="spm_speaker_name">Speaker Name</label>
        <input
            type="text"
            id="spm_speaker_name"
            name="spm_speaker_name"
            value="<?php echo esc_attr($speaker_name); ?>"
            placeholder="Gavin John Maxwell">
    </div>

    <div class="spm-field">
        <label for="spm_speaker_title">Title / Designation</label>
        <textarea
            id="spm_speaker_title"
            name="spm_speaker_title"
            rows="4"
            placeholder="Conference Chairman, Senior Principle, GBS and Business Consulting"><?php echo esc_textarea($speaker_title); ?></textarea>
    </div>

    <div class="spm-field">
        <label for="spm_speaker_company">Company Name</label>
        <input
            type="text"
            id="spm_speaker_company"
            name="spm_speaker_company"
            value="<?php echo esc_attr($company); ?>"
            placeholder="Ernst & Young">
    </div>

    <div class="spm-field">
        <label>Company Logo</label>

        <input
            type="hidden"
            id="spm_speaker_company_logo"
            name="spm_speaker_company_logo"
            value="<?php echo esc_attr($company_logo); ?>">

        <img
            id="spm_logo_preview"
            class="spm-logo-preview"
            src="<?php echo esc_url($logo_url ?: ''); ?>"
            style="<?php echo $logo_url ? '' : 'display:none;'; ?>"
            alt="">

        <button type="button" class="button" id="spm_upload_logo">
            Select Company Logo
        </button>

        <button
            type="button"
            class="button"
            id="spm_remove_logo"
            style="<?php echo $logo_url ? '' : 'display:none;'; ?>">
            Remove Logo
        </button>

        <p class="spm-help">
            Upload the company logo from the WordPress Media Library.
        </p>
    </div>

    <div class="spm-field">
        <label for="spm_speaker_ring_color">Green Ring Color</label>

        <input
            type="color"
            id="spm_speaker_ring_color"
            name="spm_speaker_ring_color"
            value="<?php echo esc_attr($ring_color); ?>">

        <p class="spm-help">
            Default color: #A4D600
        </p>
    </div>

    <p class="spm-help">
        Speaker Portrait: Use the Featured Image panel on the right.
    </p>

<?php
}


/**
 * Save Speaker Meta
 */
function spm_save_speaker_meta($post_id)
{

    if (
        !isset($_POST['spm_speaker_nonce']) ||
        !wp_verify_nonce(
            sanitize_text_field(wp_unslash($_POST['spm_speaker_nonce'])),
            'spm_save_speaker'
        )
    ) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (wp_is_post_revision($post_id)) {
        return;
    }

    if (
        !current_user_can('edit_post', $post_id) ||
        get_post_type($post_id) !== 'spm_speaker'
    ) {
        return;
    }

    $text_fields = array(
        'spm_speaker_name' => '_speaker_name',
        'spm_speaker_title' => '_speaker_title',
        'spm_speaker_company' => '_speaker_company',
    );

    foreach ($text_fields as $input => $meta_key) {

        if (isset($_POST[$input])) {

            $value = sanitize_text_field(
                wp_unslash($_POST[$input])
            );

            update_post_meta($post_id, $meta_key, $value);
        }
    }

    if (isset($_POST['spm_speaker_company_logo'])) {

        $logo_id = absint($_POST['spm_speaker_company_logo']);

        if (
            $logo_id &&
            wp_attachment_is_image($logo_id)
        ) {
            update_post_meta(
                $post_id,
                '_speaker_company_logo',
                $logo_id
            );
        } else {
            delete_post_meta($post_id, '_speaker_company_logo');
        }
    }

    if (isset($_POST['spm_speaker_ring_color'])) {

        $color = sanitize_hex_color(
            wp_unslash($_POST['spm_speaker_ring_color'])
        );

        if ($color) {
            update_post_meta(
                $post_id,
                '_speaker_ring_color',
                $color
            );
        }
    }
}
add_action('save_post_spm_speaker', 'spm_save_speaker_meta');


/**
 * Load WordPress Media Uploader
 */
function spm_admin_assets($hook)
{

    $screen = get_current_screen();

    if (
        !$screen ||
        $screen->post_type !== 'spm_speaker'
    ) {
        return;
    }

    wp_enqueue_media();

    wp_enqueue_script(
        'spm-admin',
        SPM_URL . 'assets/admin.js',
        array('jquery'),
        SPM_VERSION,
        true
    );
}
add_action('admin_enqueue_scripts', 'spm_admin_assets');


/**
 * Speaker Admin Columns
 */
function spm_speaker_columns($columns)
{

    $new_columns = array();

    $new_columns['cb'] = $columns['cb'];
    $new_columns['title'] = 'Post Title';
    $new_columns['speaker_name'] = 'Speaker Name';
    $new_columns['speaker_company'] = 'Company';
    $new_columns['speaker_image'] = 'Portrait';
    $new_columns['date'] = 'Date';

    return $new_columns;
}
add_filter('manage_spm_speaker_posts_columns', 'spm_speaker_columns');


function spm_speaker_column_content($column, $post_id)
{

    if ($column === 'speaker_name') {

        echo esc_html(
            get_post_meta($post_id, '_speaker_name', true)
        );
    }

    if ($column === 'speaker_company') {

        echo esc_html(
            get_post_meta($post_id, '_speaker_company', true)
        );
    }

    if ($column === 'speaker_image') {

        echo get_the_post_thumbnail(
            $post_id,
            array(50, 50)
        );
    }
}
add_action(
    'manage_spm_speaker_posts_custom_column',
    'spm_speaker_column_content',
    10,
    2
);


/**
 * Add speaker data attributes to singular speaker pages.
 * Useful for custom CSS and frontend integrations.
 */
function spm_speaker_body_class($classes)
{

    if (is_singular('spm_speaker')) {
        $classes[] = 'spm-single-speaker';
    }

    return $classes;
}
add_filter('body_class', 'spm_speaker_body_class');


/**
 * Flush rewrite rules on activation/deactivation
 */
function spm_activate()
{
    spm_register_speaker_cpt();
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'spm_activate');

function spm_deactivate()
{
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'spm_deactivate');
