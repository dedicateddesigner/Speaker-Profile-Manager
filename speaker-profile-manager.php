<?php
/**
 * Plugin Name: Speaker Profile Manager
 * Description: Manage speaker profiles and expose speaker fields as Elementor Dynamic Tags.
 * Version: 1.1.0
 * Author: Dedicated Designer
 * Text Domain: speaker-profile-manager
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */
if (!defined('ABSPATH')) exit;

define('SPM_VERSION', '1.1.0');
define('SPM_URL', plugin_dir_url(__FILE__));

function spm_register_speaker_cpt() {
    register_post_type('spm_speaker', array(
        'labels' => array(
            'name' => __('Speakers', 'speaker-profile-manager'),
            'singular_name' => __('Speaker', 'speaker-profile-manager'),
            'menu_name' => __('Speakers', 'speaker-profile-manager'),
            'add_new_item' => __('Add New Speaker', 'speaker-profile-manager'),
            'edit_item' => __('Edit Speaker', 'speaker-profile-manager'),
        ),
        'public' => true,
        'show_in_rest' => true,
        'menu_icon' => 'dashicons-groups',
        'menu_position' => 20,
        'supports' => array('title', 'thumbnail', 'revisions'),
        'has_archive' => true,
        'rewrite' => array('slug' => 'speakers', 'with_front' => false),
    ));
}
add_action('init', 'spm_register_speaker_cpt');

function spm_register_meta_fields() {
    foreach (array(
        '_speaker_name' => 'string',
        '_speaker_title' => 'string',
        '_speaker_company' => 'string',
        '_speaker_company_logo' => 'integer',
        '_speaker_ring_color' => 'string',
    ) as $key => $type) {
        register_post_meta('spm_speaker', $key, array(
            'type' => $type,
            'single' => true,
            'show_in_rest' => true,
            'sanitize_callback' => 'spm_sanitize_meta',
            'auth_callback' => function () { return current_user_can('edit_posts'); },
        ));
    }
}
add_action('init', 'spm_register_meta_fields');

function spm_sanitize_meta($value) {
    return (is_scalar($value) ? sanitize_text_field((string) $value) : '');
}

function spm_add_meta_box() {
    add_meta_box('spm_speaker_details', __('Speaker Information', 'speaker-profile-manager'), 'spm_render_meta_box', 'spm_speaker', 'normal', 'high');
}
add_action('add_meta_boxes', 'spm_add_meta_box');

function spm_render_meta_box($post) {
    wp_nonce_field('spm_save_speaker', 'spm_speaker_nonce');
    $name = get_post_meta($post->ID, '_speaker_name', true);
    $title = get_post_meta($post->ID, '_speaker_title', true);
    $company = get_post_meta($post->ID, '_speaker_company', true);
    $logo = absint(get_post_meta($post->ID, '_speaker_company_logo', true));
    $color = get_post_meta($post->ID, '_speaker_ring_color', true) ?: '#A4D600';
    $logo_url = $logo ? wp_get_attachment_image_url($logo, 'medium') : '';
    ?>
    <style>
    .spm-field{margin:0 0 22px}.spm-field label{display:block;font-weight:600;margin-bottom:7px}
    .spm-field input[type=text],.spm-field textarea{width:100%;max-width:650px}
    .spm-logo-preview{display:block;max-width:240px;max-height:120px;margin:10px 0;object-fit:contain}
    .spm-help{color:#646970;font-size:12px}
    </style>
    <div class="spm-field">
      <label for="spm_speaker_name">Speaker Name</label>
      <input type="text" id="spm_speaker_name" name="spm_speaker_name" value="<?php echo esc_attr($name); ?>" placeholder="Gavin John Maxwell">
    </div>
    <div class="spm-field">
      <label for="spm_speaker_title">Title / Designation</label>
      <textarea id="spm_speaker_title" name="spm_speaker_title" rows="4"><?php echo esc_textarea($title); ?></textarea>
    </div>
    <div class="spm-field">
      <label for="spm_speaker_company">Company Name</label>
      <input type="text" id="spm_speaker_company" name="spm_speaker_company" value="<?php echo esc_attr($company); ?>" placeholder="Ernst & Young">
    </div>
    <div class="spm-field">
      <label>Company Logo (recommended: 600 × 300 px)</label>
      <input type="hidden" id="spm_speaker_company_logo" name="spm_speaker_company_logo" value="<?php echo esc_attr($logo); ?>">
      <img id="spm_logo_preview" class="spm-logo-preview" src="<?php echo esc_url($logo_url ?: ''); ?>" style="<?php echo $logo_url ? '' : 'display:none;'; ?>" alt="">
      <button type="button" class="button" id="spm_upload_logo">Select Company Logo</button>
      <button type="button" class="button" id="spm_remove_logo" style="<?php echo $logo_url ? '' : 'display:none;'; ?>">Remove Logo</button>
      <p class="spm-help">Use the same logo for the speaker profile and marquee. Preserve its ratio with Object Fit: Contain in Elementor.</p>
    </div>
    <div class="spm-field">
      <label for="spm_speaker_ring_color">Speaker Ring Color</label>
      <input type="color" id="spm_speaker_ring_color" name="spm_speaker_ring_color" value="<?php echo esc_attr($color); ?>">
    </div>
    <p class="spm-help">Speaker portrait: use the Featured Image panel.</p>
    <?php
}

function spm_save_speaker_meta($post_id) {
    if (!isset($_POST['spm_speaker_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['spm_speaker_nonce'])), 'spm_save_speaker')) return;
    if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || wp_is_post_revision($post_id)) return;
    if (!current_user_can('edit_post', $post_id) || get_post_type($post_id) !== 'spm_speaker') return;

    foreach (array(
        'spm_speaker_name' => '_speaker_name',
        'spm_speaker_title' => '_speaker_title',
        'spm_speaker_company' => '_speaker_company',
    ) as $input => $meta_key) {
        if (isset($_POST[$input])) update_post_meta($post_id, $meta_key, sanitize_text_field(wp_unslash($_POST[$input])));
    }

    if (isset($_POST['spm_speaker_company_logo'])) {
        $id = absint($_POST['spm_speaker_company_logo']);
        if ($id && wp_attachment_is_image($id)) update_post_meta($post_id, '_speaker_company_logo', $id);
        else delete_post_meta($post_id, '_speaker_company_logo');
    }

    if (isset($_POST['spm_speaker_ring_color'])) {
        $color = sanitize_hex_color(wp_unslash($_POST['spm_speaker_ring_color']));
        if ($color) update_post_meta($post_id, '_speaker_ring_color', $color);
    }
}
add_action('save_post_spm_speaker', 'spm_save_speaker_meta');

function spm_admin_assets() {
    $screen = get_current_screen();
    if (!$screen || $screen->post_type !== 'spm_speaker') return;
    wp_enqueue_media();
    wp_enqueue_script('spm-admin', SPM_URL . 'assets/admin.js', array('jquery'), SPM_VERSION, true);
}
add_action('admin_enqueue_scripts', 'spm_admin_assets');

function spm_speaker_columns($columns) {
    return array(
        'cb' => $columns['cb'] ?? '',
        'title' => 'Post Title',
        'speaker_name' => 'Speaker Name',
        'speaker_company' => 'Company',
        'speaker_image' => 'Portrait',
        'date' => 'Date',
    );
}
add_filter('manage_spm_speaker_posts_columns', 'spm_speaker_columns');

function spm_speaker_column_content($column, $post_id) {
    if ($column === 'speaker_name') echo esc_html(get_post_meta($post_id, '_speaker_name', true));
    if ($column === 'speaker_company') echo esc_html(get_post_meta($post_id, '_speaker_company', true));
    if ($column === 'speaker_image') echo get_the_post_thumbnail($post_id, array(50, 50));
}
add_action('manage_spm_speaker_posts_custom_column', 'spm_speaker_column_content', 10, 2);

/**
 * Elementor Dynamic Tags: text fields and company logo image.
 */
function spm_register_elementor_dynamic_tags($dynamic_tags) {
    if (!did_action('elementor/loaded') || !class_exists('\Elementor\Core\DynamicTags\Tag')) return;

    if (method_exists($dynamic_tags, 'register_group')) {
        $dynamic_tags->register_group('spm', array('title' => __('Speaker Profile Manager', 'speaker-profile-manager')));
    }

    foreach (array(
        array('_speaker_name', 'Speaker Name'),
        array('_speaker_title', 'Speaker Title'),
        array('_speaker_company', 'Speaker Company'),
        array('_speaker_ring_color', 'Speaker Ring Color'),
    ) as $field) {
        $dynamic_tags->register(new SPM_Elementor_Text_Tag($field[0], $field[1], $field[0]));
    }

    if (class_exists('\Elementor\Core\DynamicTags\Data_Tag')) {
        $dynamic_tags->register(new SPM_Elementor_Company_Logo_Tag());
    }
}
add_action('elementor/dynamic_tags/register', 'spm_register_elementor_dynamic_tags');

if (class_exists('\Elementor\Core\DynamicTags\Tag')) {
    class SPM_Elementor_Text_Tag extends \Elementor\Core\DynamicTags\Tag {
        private $spm_key;
        private $spm_label;

        public function __construct($key, $label, $meta_key) {
            $this->spm_key = $meta_key;
            $this->spm_label = $label;
            parent::__construct();
        }
        public function get_name() { return 'spm-' . sanitize_key($this->spm_key); }
        public function get_title() { return __($this->spm_label, 'speaker-profile-manager'); }
        public function get_group() { return 'spm'; }
        public function get_categories() { return array(\Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY); }
        public function render() {
            $value = get_post_meta(get_the_ID(), $this->spm_key, true);
            if ($this->spm_key === '_speaker_ring_color') $value = sanitize_hex_color($value) ?: '#A4D600';
            echo esc_html($value);
        }
    }
}

if (class_exists('\Elementor\Core\DynamicTags\Data_Tag')) {
    class SPM_Elementor_Company_Logo_Tag extends \Elementor\Core\DynamicTags\Data_Tag {
        public function get_name() { return 'spm-company-logo'; }
        public function get_title() { return __('Speaker Company Logo', 'speaker-profile-manager'); }
        public function get_group() { return 'spm'; }
        public function get_categories() { return array(\Elementor\Modules\DynamicTags\Module::IMAGE_CATEGORY); }
        public function get_value(array $options = array()) {
            $id = absint(get_post_meta(get_the_ID(), '_speaker_company_logo', true));
            if (!$id || !wp_attachment_is_image($id)) return array();
            $size = $options['size'] ?? 'full';
            $image = wp_get_attachment_image_src($id, $size);
            if (!$image) return array();
            return array('id' => $id, 'url' => $image[0]);
        }
    }
}

function spm_activate() {
    spm_register_speaker_cpt();
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'spm_activate');
function spm_deactivate() { flush_rewrite_rules(); }
register_deactivation_hook(__FILE__, 'spm_deactivate');
