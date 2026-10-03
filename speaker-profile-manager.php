<?php
/**
 * Plugin Name: Speaker Profile Manager
 * Description: Manage speakers, sponsors and media partners with Elementor marquee widgets.
 * Version: 1.4.3
 * Author: Dedicated Designer
 * Text Domain: speaker-profile-manager
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */
if (!defined('ABSPATH')) exit;

define('SPM_VERSION', '1.4.3');
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
        'supports' => array('title', 'thumbnail', 'revisions', 'page-attributes'),
        'has_archive' => true,
        'rewrite' => array('slug' => 'speakers', 'with_front' => false),
    ));
}

add_action('init', 'spm_register_speaker_cpt');

function spm_register_partner_cpts() {
    foreach (array('spm_sponsor' => 'Sponsor', 'spm_media_partner' => 'Media Partner') as $type => $label) {
        register_post_type($type, array(
            'labels' => array('name' => __($label . 's', 'speaker-profile-manager'), 'singular_name' => __($label, 'speaker-profile-manager'), 'add_new_item' => __('Add New ' . $label, 'speaker-profile-manager')),
            'public' => false, 'show_ui' => true, 'show_in_rest' => true,
            'menu_icon' => $type === 'spm_sponsor' ? 'dashicons-awards' : 'dashicons-megaphone',
            'supports' => array('title', 'thumbnail', 'page-attributes'),
        ));
    }
}
add_action('init', 'spm_register_partner_cpts');

function spm_partner_meta_boxes() {
    add_meta_box('spm_partner_details', __('Partner Details', 'speaker-profile-manager'), 'spm_render_partner_meta', array('spm_sponsor','spm_media_partner'), 'normal', 'high');
}
add_action('add_meta_boxes', 'spm_partner_meta_boxes');
function spm_render_partner_meta($post) {
    wp_nonce_field('spm_save_partner', 'spm_partner_nonce');
    $logo = absint(get_post_meta($post->ID, '_spm_partner_logo', true));
    $tier = get_post_meta($post->ID, '_spm_partner_tier', true);
    $url = get_post_meta($post->ID, '_spm_partner_url', true);
    $order = get_post_meta($post->ID, '_spm_partner_order', true);
    $logo_url = $logo ? wp_get_attachment_image_url($logo, 'medium') : '';
    ?>
    <p><label><strong><?php esc_html_e('Logo', 'speaker-profile-manager'); ?></strong></label><br>
    <input type="hidden" id="spm_partner_logo" name="spm_partner_logo" value="<?php echo esc_attr($logo); ?>">
    <img id="spm_partner_logo_preview" src="<?php echo esc_url($logo_url); ?>" style="display:<?php echo $logo_url ? 'block' : 'none'; ?>;max-width:320px;max-height:140px;object-fit:contain;margin:12px 0">
    <button type="button" class="button" id="spm_partner_upload"><?php esc_html_e('Select Logo', 'speaker-profile-manager'); ?></button>
    <button type="button" class="button" id="spm_partner_remove"><?php esc_html_e('Remove', 'speaker-profile-manager'); ?></button></p>
    <?php if ($post->post_type === 'spm_sponsor') : ?>
    <p><label for="spm_partner_tier"><strong><?php esc_html_e('Partner Tier / Label', 'speaker-profile-manager'); ?></strong></label><br><input type="text" class="widefat" id="spm_partner_tier" name="spm_partner_tier" value="<?php echo esc_attr($tier); ?>" placeholder="Silver Partner"></p>
    <?php endif; ?>
    <p><label for="spm_partner_url"><strong><?php esc_html_e('Website URL (optional)', 'speaker-profile-manager'); ?></strong></label><br><input type="url" class="widefat" id="spm_partner_url" name="spm_partner_url" value="<?php echo esc_attr($url); ?>"></p>
    <p><label for="spm_partner_order"><strong><?php esc_html_e('Display Order', 'speaker-profile-manager'); ?></strong></label><br><input type="number" min="1" id="spm_partner_order" name="spm_partner_order" value="<?php echo esc_attr($order === '' ? '1' : $order); ?>"></p>
    <?php
}
function spm_save_partner_meta($post_id) {
    if (!isset($_POST['spm_partner_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['spm_partner_nonce'])), 'spm_save_partner')) return;
    if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || wp_is_post_revision($post_id) || !current_user_can('edit_post', $post_id)) return;
    if (!in_array(get_post_type($post_id), array('spm_sponsor','spm_media_partner'), true)) return;
    if (isset($_POST['spm_partner_logo'])) { $id=absint($_POST['spm_partner_logo']); if ($id && wp_attachment_is_image($id)) update_post_meta($post_id,'_spm_partner_logo',$id); else delete_post_meta($post_id,'_spm_partner_logo'); }
    if (isset($_POST['spm_partner_tier'])) update_post_meta($post_id,'_spm_partner_tier',sanitize_text_field(wp_unslash($_POST['spm_partner_tier'])));
    if (isset($_POST['spm_partner_url'])) update_post_meta($post_id,'_spm_partner_url',esc_url_raw(wp_unslash($_POST['spm_partner_url'])));
    if (isset($_POST['spm_partner_order'])) update_post_meta($post_id,'_spm_partner_order',max(1,absint($_POST['spm_partner_order'])));
}
add_action('save_post', 'spm_save_partner_meta');


function spm_register_meta_fields() {
    foreach (array('_speaker_name' => 'string', '_speaker_title' => 'string', '_speaker_company' => 'string', '_speaker_company_logo' => 'integer', '_speaker_order' => 'integer') as $key => $type) {
        register_post_meta('spm_speaker', $key, array(
            'type' => $type, 'single' => true, 'show_in_rest' => true,
            'sanitize_callback' => 'spm_sanitize_meta',
            'auth_callback' => function () { return current_user_can('edit_posts'); },
        ));
    }
}
add_action('init', 'spm_register_meta_fields');
function spm_sanitize_meta($value) { return is_scalar($value) ? sanitize_text_field((string) $value) : ''; }

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
    $order = get_post_meta($post->ID, '_speaker_order', true);
    $logo_url = $logo ? wp_get_attachment_image_url($logo, 'medium') : '';
    ?>
    <style>.spm-field{margin:0 0 22px}.spm-field label{display:block;font-weight:600;margin-bottom:7px}.spm-field input[type=text],.spm-field input[type=number],.spm-field textarea{width:100%;max-width:650px}.spm-logo-preview{display:block;max-width:240px;max-height:120px;margin:10px 0;object-fit:contain}.spm-help{color:#646970;font-size:12px}</style>
    <div class="spm-field"><label for="spm_speaker_name">Speaker Name</label><input type="text" id="spm_speaker_name" name="spm_speaker_name" value="<?php echo esc_attr($name); ?>" placeholder="Gavin John Maxwell"></div>
    <div class="spm-field"><label for="spm_speaker_title">Title / Designation</label><textarea id="spm_speaker_title" name="spm_speaker_title" rows="4"><?php echo esc_textarea($title); ?></textarea></div>
    <div class="spm-field"><label for="spm_speaker_company">Company Name</label><input type="text" id="spm_speaker_company" name="spm_speaker_company" value="<?php echo esc_attr($company); ?>" placeholder="Ernst & Young"></div>
    <div class="spm-field">
      <label>Company Logo (recommended: 600 × 300 px)</label>
      <input type="hidden" id="spm_speaker_company_logo" name="spm_speaker_company_logo" value="<?php echo esc_attr($logo); ?>">
      <img id="spm_logo_preview" class="spm-logo-preview" src="<?php echo esc_url($logo_url ?: ''); ?>" style="<?php echo $logo_url ? '' : 'display:none;'; ?>" alt="">
      <button type="button" class="button" id="spm_upload_logo">Select Company Logo</button>
      <button type="button" class="button" id="spm_remove_logo" style="<?php echo $logo_url ? '' : 'display:none;'; ?>">Remove Logo</button>
      <p class="spm-help">Use the same logo in the profile and marquee. Set Object Fit: Contain in Elementor.</p>
    </div>
    <div class="spm-field"><label for="spm_speaker_order">Display Order</label><input type="number" min="1" step="1" id="spm_speaker_order" name="spm_speaker_order" value="<?php echo esc_attr($order === '' ? '1' : $order); ?>"><p class="spm-help">1 appears first, 2 appears second, and so on. Use unique numbers.</p></div>
    <p class="spm-help">Speaker portrait: use the Featured Image panel.</p>
    <?php
}
function spm_save_speaker_meta($post_id) {
    if (!isset($_POST['spm_speaker_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['spm_speaker_nonce'])), 'spm_save_speaker')) return;
    if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || wp_is_post_revision($post_id)) return;
    if (!current_user_can('edit_post', $post_id) || get_post_type($post_id) !== 'spm_speaker') return;
    foreach (array('spm_speaker_name'=>'_speaker_name','spm_speaker_title'=>'_speaker_title','spm_speaker_company'=>'_speaker_company') as $input=>$key) {
        if (isset($_POST[$input])) update_post_meta($post_id, $key, sanitize_text_field(wp_unslash($_POST[$input])));
    }
    if (isset($_POST['spm_speaker_company_logo'])) {
        $id = absint($_POST['spm_speaker_company_logo']);
        if ($id && wp_attachment_is_image($id)) update_post_meta($post_id, '_speaker_company_logo', $id);
        else delete_post_meta($post_id, '_speaker_company_logo');
    }
    if (isset($_POST['spm_speaker_order'])) update_post_meta($post_id, '_speaker_order', max(1, absint($_POST['spm_speaker_order'])));
}
add_action('save_post_spm_speaker', 'spm_save_speaker_meta');

function spm_admin_assets($hook) {
    $screen = get_current_screen();
    if (!$screen || !in_array($screen->post_type, array('spm_speaker','spm_sponsor','spm_media_partner'), true)) return;
    if ($hook === 'post.php' || $hook === 'post-new.php') wp_enqueue_media();
    wp_enqueue_script('spm-admin', SPM_URL . 'assets/admin.js', array('jquery'), SPM_VERSION, true);
    if ($hook === 'edit.php') wp_enqueue_script('spm-quick-edit', SPM_URL . 'assets/quick-edit.js', array('jquery','inline-edit-post'), SPM_VERSION, true);
}
add_action('admin_enqueue_scripts', 'spm_admin_assets');

function spm_speaker_columns($columns) {
    return array('cb'=>$columns['cb'] ?? '', 'title'=>'Post Title', 'speaker_name'=>'Speaker Name', 'speaker_company'=>'Company', 'speaker_order'=>'Order', 'speaker_image'=>'Portrait', 'date'=>'Date');
}
add_filter('manage_spm_speaker_posts_columns', 'spm_speaker_columns');
function spm_speaker_column_content($column, $post_id) {
    if ($column === 'speaker_name') echo esc_html(get_post_meta($post_id, '_speaker_name', true));
    if ($column === 'speaker_company') echo esc_html(get_post_meta($post_id, '_speaker_company', true));
    if ($column === 'speaker_order') { $order=get_post_meta($post_id,'_speaker_order',true); echo esc_html($order===''?'1':$order); echo '<div class="hidden" id="spm-order-'.esc_attr($post_id).'">'.esc_html($order===''?'1':$order).'</div>'; }
    if ($column === 'speaker_image') echo get_the_post_thumbnail($post_id, array(50,50));
}
add_action('manage_spm_speaker_posts_custom_column', 'spm_speaker_column_content', 10, 2);
add_filter('manage_edit-spm_speaker_sortable_columns', function($columns){$columns['speaker_order']='speaker_order';return $columns;});
add_action('pre_get_posts', function($query){if(is_admin()&&$query->is_main_query()&&$query->get('orderby')==='speaker_order'){$query->set('meta_key','_speaker_order');$query->set('orderby','meta_value_num');}});

function spm_quick_edit_order_field($column_name, $post_type) {
    if ($post_type !== 'spm_speaker' || $column_name !== 'speaker_order') return;
    ?>
    <fieldset class="inline-edit-col-right"><div class="inline-edit-col"><label><span class="title">Display Order</span><span class="input-text-wrap"><input type="number" min="1" step="1" name="spm_speaker_order" class="spm-quick-order" value=""></span></label></div></fieldset>
    <?php
}
add_action('quick_edit_custom_box', 'spm_quick_edit_order_field', 10, 2);
function spm_save_quick_edit_order($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!isset($_POST['spm_speaker_order']) || !current_user_can('edit_post',$post_id) || get_post_type($post_id)!=='spm_speaker') return;
    update_post_meta($post_id, '_speaker_order', max(1, absint($_POST['spm_speaker_order'])));
}
add_action('save_post_spm_speaker', 'spm_save_quick_edit_order', 20);

function spm_register_elementor_dynamic_tags($dynamic_tags) {
    if (!did_action('elementor/loaded') || !class_exists('\Elementor\Core\DynamicTags\Tag')) return;
    if (method_exists($dynamic_tags,'register_group')) $dynamic_tags->register_group('spm', array('title'=>__('Speaker Profile Manager','speaker-profile-manager')));
    foreach (array(array('_speaker_name','Speaker Name'),array('_speaker_title','Speaker Title'),array('_speaker_company','Speaker Company')) as $field) $dynamic_tags->register(new SPM_Elementor_Text_Tag($field[0],$field[1]));
    if (class_exists('\Elementor\Core\DynamicTags\Data_Tag')) $dynamic_tags->register(new SPM_Elementor_Company_Logo_Tag());
}
add_action('elementor/dynamic_tags/register', 'spm_register_elementor_dynamic_tags');
if (class_exists('\Elementor\Core\DynamicTags\Tag')) {
    class SPM_Elementor_Text_Tag extends \Elementor\Core\DynamicTags\Tag {
        private $spm_key; private $spm_label;
        public function __construct($key,$label){$this->spm_key=$key;$this->spm_label=$label;parent::__construct();}
        public function get_name(){return 'spm-'.sanitize_key($this->spm_key);}
        public function get_title(){return __($this->spm_label,'speaker-profile-manager');}
        public function get_group(){return 'spm';}
        public function get_categories(){return array(\Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY);}
        public function render(){echo esc_html(get_post_meta(get_the_ID(),$this->spm_key,true));}
    }
}
if (class_exists('\Elementor\Core\DynamicTags\Data_Tag')) {
    class SPM_Elementor_Company_Logo_Tag extends \Elementor\Core\DynamicTags\Data_Tag {
        public function get_name(){return 'spm-company-logo';}
        public function get_title(){return __('Speaker Company Logo','speaker-profile-manager');}
        public function get_group(){return 'spm';}
        public function get_categories(){return array(\Elementor\Modules\DynamicTags\Module::IMAGE_CATEGORY);}
        public function get_value(array $options=array()){
            $id=absint(get_post_meta(get_the_ID(),'_speaker_company_logo',true));
            if(!$id||!wp_attachment_is_image($id))return array();
            $image=wp_get_attachment_image_src($id,$options['size']??'full');
            return $image?array('id'=>$id,'url'=>$image[0]):array();
        }
    }
}

/* Elementor widget */
function spm_register_elementor_widget($widgets_manager) {
    if (!did_action('elementor/loaded') || !class_exists('\Elementor\Widget_Base')) return;
    if (!class_exists('SPM_Speaker_Marquee_Widget')) {
        class SPM_Speaker_Marquee_Widget extends \Elementor\Widget_Base {
            public function get_name(){return 'spm-speaker-marquee';}
            public function get_title(){return __('Speaker Marquee','speaker-profile-manager');}
            public function get_icon(){return 'eicon-carousel';}
            public function get_categories(){return array('general');}
            public function get_style_depends(){return array('spm-marquee');}
            protected function register_controls(){
                $templates=array('0'=>__('Use built-in speaker card','speaker-profile-manager'));
                $saved=get_posts(array('post_type'=>'elementor_library','post_status'=>'publish','numberposts'=>-1,'orderby'=>'title','order'=>'ASC'));
                foreach($saved as $template)$templates[$template->ID]=$template->post_title;
                $this->start_controls_section('spm_content',array('label'=>__('Speakers','speaker-profile-manager'),'tab'=>\Elementor\Controls_Manager::TAB_CONTENT));
                $this->add_control('card_template',array('label'=>__('Elementor Card Template','speaker-profile-manager'),'type'=>\Elementor\Controls_Manager::SELECT,'options'=>$templates,'default'=>'0','description'=>__('Choose a saved Elementor template. Leave default to use the built-in card.','speaker-profile-manager')));
                $this->add_control('limit',array('label'=>__('Number of Speakers','speaker-profile-manager'),'type'=>\Elementor\Controls_Manager::NUMBER,'min'=>1,'default'=>6));
                $this->add_responsive_control('card_width',array('label'=>__('Card Width (px)','speaker-profile-manager'),'type'=>\Elementor\Controls_Manager::SLIDER,'size_units'=>array('px'),'range'=>array('px'=>array('min'=>220,'max'=>600)),'default'=>array('unit'=>'px','size'=>420),'tablet_default'=>array('unit'=>'px','size'=>340),'mobile_default'=>array('unit'=>'px','size'=>280),'selectors'=>array('{{WRAPPER}} .spm-speaker-card'=>'width: {{SIZE}}{{UNIT}};')));
                $this->add_responsive_control('portrait_size',array('label'=>__('Portrait Size (px)','speaker-profile-manager'),'type'=>\Elementor\Controls_Manager::SLIDER,'size_units'=>array('px'),'range'=>array('px'=>array('min'=>80,'max'=>220)),'default'=>array('unit'=>'px','size'=>170),'tablet_default'=>array('unit'=>'px','size'=>145),'mobile_default'=>array('unit'=>'px','size'=>118),'selectors'=>array('{{WRAPPER}} .spm-portrait'=>'width:{{SIZE}}{{UNIT}};height:{{SIZE}}{{UNIT}};flex-basis:{{SIZE}}{{UNIT}};')));
                $this->add_responsive_control('card_gap',array('label'=>__('Gap (px)','speaker-profile-manager'),'type'=>\Elementor\Controls_Manager::SLIDER,'size_units'=>array('px'),'range'=>array('px'=>array('min'=>0,'max'=>80)),'default'=>array('unit'=>'px','size'=>24),'tablet_default'=>array('unit'=>'px','size'=>18),'mobile_default'=>array('unit'=>'px','size'=>12),'selectors'=>array('{{WRAPPER}} .spm-marquee-track'=>'gap: {{SIZE}}{{UNIT}};')));
                $this->add_control('show_view_all',array('label'=>__('Show View All Button','speaker-profile-manager'),'type'=>\Elementor\Controls_Manager::SWITCHER,'default'=>''));
                $this->add_control('view_all_text',array('label'=>__('Button Text','speaker-profile-manager'),'type'=>\Elementor\Controls_Manager::TEXT,'default'=>__('View All Speakers','speaker-profile-manager'),'condition'=>array('show_view_all'=>'yes')));
                $this->add_control('view_all_url',array('label'=>__('Button Link','speaker-profile-manager'),'type'=>\Elementor\Controls_Manager::URL,'placeholder'=>'https://example.com/speakers/','default'=>array('url'=>''),'condition'=>array('show_view_all'=>'yes')));
                $this->add_control('view_all_align',array('label'=>__('Button Alignment','speaker-profile-manager'),'type'=>\Elementor\Controls_Manager::CHOOSE,'options'=>array('left'=>array('title'=>__('Left','speaker-profile-manager'),'icon'=>'eicon-text-align-left'),'center'=>array('title'=>__('Center','speaker-profile-manager'),'icon'=>'eicon-text-align-center'),'right'=>array('title'=>__('Right','speaker-profile-manager'),'icon'=>'eicon-text-align-right')),'default'=>'center','condition'=>array('show_view_all'=>'yes')));
                $this->add_control('view_all_color',array('label'=>__('Button Text Color','speaker-profile-manager'),'type'=>\Elementor\Controls_Manager::COLOR,'default'=>'#242B53','selectors'=>array('{{WRAPPER}} .spm-view-all'=>'color:{{VALUE}};'),'condition'=>array('show_view_all'=>'yes')));
                $this->add_control('view_all_bg',array('label'=>__('Button Background','speaker-profile-manager'),'type'=>\Elementor\Controls_Manager::COLOR,'default'=>'#A4D600','selectors'=>array('{{WRAPPER}} .spm-view-all'=>'background-color:{{VALUE}};'),'condition'=>array('show_view_all'=>'yes')));
                $this->add_control('speed',array('label'=>__('Marquee Duration (seconds)','speaker-profile-manager'),'type'=>\Elementor\Controls_Manager::NUMBER,'min'=>5,'default'=>28));
                $this->add_control('direction',array('label'=>__('Direction','speaker-profile-manager'),'type'=>\Elementor\Controls_Manager::SELECT,'options'=>array('left'=>__('Left','speaker-profile-manager'),'right'=>__('Right','speaker-profile-manager')),'default'=>'left'));
                $this->add_control('pause_hover',array('label'=>__('Pause on Hover','speaker-profile-manager'),'type'=>\Elementor\Controls_Manager::SWITCHER,'default'=>'yes'));
                $this->add_control('card_bg',array('label'=>__('Card Background','speaker-profile-manager'),'type'=>\Elementor\Controls_Manager::COLOR,'default'=>'#ffffff','selectors'=>array('{{WRAPPER}} .spm-speaker-card'=>'background-color: {{VALUE}};')));
                $this->add_control('accent',array('label'=>__('Accent Color','speaker-profile-manager'),'type'=>\Elementor\Controls_Manager::COLOR,'default'=>'#A4D600','selectors'=>array('{{WRAPPER}} .spm-speaker-name, {{WRAPPER}} .spm-portrait'=>'--spm-accent: {{VALUE}};')));
                $this->end_controls_section();
            }
            private function render_card($post_id,$template_id){
                global $post;
                $old_post=$post;
                $post=get_post($post_id);
                if(!$post)return;
                setup_postdata($post);
                if($template_id && class_exists('\Elementor\Plugin')){
                    echo \Elementor\Plugin::instance()->frontend->get_builder_content_for_display($template_id,true);
                } else {
                    $name=get_post_meta($post_id,'_speaker_name',true) ?: get_the_title($post_id);
                    $title=get_post_meta($post_id,'_speaker_title',true);
                    $company=get_post_meta($post_id,'_speaker_company',true);
                    $logo=absint(get_post_meta($post_id,'_speaker_company_logo',true));
                    echo '<article class="spm-speaker-card">';
                    echo '<div class="spm-portrait">';
                    if(has_post_thumbnail($post_id)) echo get_the_post_thumbnail($post_id,'large',array('class'=>'spm-portrait-img','loading'=>'lazy'));
                    echo '</div>';
                    echo '<h3 class="spm-speaker-name">'.esc_html($name).'</h3>';
                    if($title)echo '<div class="spm-speaker-title">'.esc_html($title).'</div>';
                    if($logo)echo '<div class="spm-company-logo">'.wp_get_attachment_image($logo,'medium',false,array('loading'=>'lazy')).'</div>';
                    elseif($company)echo '<div class="spm-company-name">'.esc_html($company).'</div>';
                    echo '</article>';
                }
                wp_reset_postdata();
                $post=$old_post;
                if($post)setup_postdata($post);
            }
            protected function render(){
                $s=$this->get_settings_for_display();
                $limit=max(1,absint($s['limit']?:6));
                $q=new \WP_Query(array('post_type'=>'spm_speaker','post_status'=>'publish','posts_per_page'=>$limit,'meta_key'=>'_speaker_order','orderby'=>array('meta_value_num'=>'ASC','date'=>'ASC'),'order'=>'ASC','no_found_rows'=>true));
                if(!$q->have_posts())return;
                $items=$q->posts;
                $count=count($items);
                $template=absint($s['card_template']??0);
                $duration=max(5,absint($s['speed']?:28));
                $direction=($s['direction']??'left')==='right'?'right':'left';
                $pause=($s['pause_hover']??'yes')==='yes'?' is-pause-hover':'';
                if($count===1){
                    echo '<div class="spm-marquee spm-single"><div class="spm-single-inner">';
                    $this->render_card($items[0]->ID,$template);
                    echo '</div></div>';
                } else {
                    echo '<div class="spm-marquee'.$pause.'" style="--spm-duration:'.esc_attr($duration).'s;--spm-direction:'.esc_attr($direction==='right'?'reverse':'normal').';">';
                    echo '<div class="spm-marquee-track">';
                    foreach(array(0,1) as $copy)foreach($items as $item){echo '<div class="spm-marquee-item" aria-hidden="'.($copy?'true':'false').'">';$this->render_card($item->ID,$template);echo '</div>';}
                    echo '</div></div>';
                }
                if (($s['show_view_all'] ?? '') === 'yes' && !empty($s['view_all_url']['url'])) {
                    $url = $s['view_all_url']['url'];
                    $target = !empty($s['view_all_url']['is_external']) ? ' target="_blank"' : '';
                    $nofollow = !empty($s['view_all_url']['nofollow']) ? ' rel="nofollow"' : '';
                    $align = in_array(($s['view_all_align'] ?? 'center'), array('left','center','right'), true) ? $s['view_all_align'] : 'center';
                    echo '<div class="spm-view-all-wrap" style="text-align:'.esc_attr($align).'"><a class="spm-view-all" href="'.esc_url($url).'"'.$target.$nofollow.'>'.esc_html($s['view_all_text'] ?: __('View All Speakers','speaker-profile-manager')).'</a></div>';
                }
                wp_reset_postdata();
            }
        }
    }
    $widgets_manager->register(new \SPM_Speaker_Marquee_Widget());
}
add_action('elementor/widgets/register','spm_register_elementor_widget');


function spm_register_partner_widgets($widgets_manager) {
    if (!class_exists('\Elementor\Widget_Base')) return;
    foreach (array('sponsor'=>'spm_sponsor','media'=>'spm_media_partner') as $kind=>$post_type) {
        $class = $kind === 'sponsor' ? 'SPM_Sponsor_Marquee_Widget' : 'SPM_Media_Partner_Marquee_Widget';
        if (class_exists($class)) continue;
        if ($kind === 'sponsor') {
            class SPM_Sponsor_Marquee_Widget extends \Elementor\Widget_Base {
                public function get_name(){return 'spm-sponsor-marquee';}
                public function get_title(){return __('Sponsor Marquee','speaker-profile-manager');}
                public function get_icon(){return 'eicon-logo';}
                public function get_categories(){return array('general');}
                public function get_style_depends(){return array('spm-marquee');}
                protected function register_controls(){
                    $this->start_controls_section('content',array('label'=>__('Sponsors','speaker-profile-manager')));
                    $this->add_control('limit',array('label'=>__('Number of Sponsors','speaker-profile-manager'),'type'=>\Elementor\Controls_Manager::NUMBER,'min'=>1,'default'=>12));
                    $this->add_control('speed',array('label'=>__('Duration (seconds)','speaker-profile-manager'),'type'=>\Elementor\Controls_Manager::NUMBER,'min'=>5,'default'=>28));
                    $this->add_control('direction',array('label'=>__('Direction','speaker-profile-manager'),'type'=>\Elementor\Controls_Manager::SELECT,'options'=>array('left'=>__('Right to Left','speaker-profile-manager'),'right'=>__('Left to Right','speaker-profile-manager')),'default'=>'left'));
                    $this->add_control('pause_hover',array('label'=>__('Pause on Hover','speaker-profile-manager'),'type'=>\Elementor\Controls_Manager::SWITCHER,'default'=>'yes'));
                    $this->add_control('card_width',array('label'=>__('Card Width (px)','speaker-profile-manager'),'type'=>\Elementor\Controls_Manager::SLIDER,'size_units'=>array('px'),'range'=>array('px'=>array('min'=>160,'max'=>600)),'default'=>array('unit'=>'px','size'=>320),'selectors'=>array('{{WRAPPER}} .spm-partner-card'=>'width:{{SIZE}}{{UNIT}};')));
                    $this->add_responsive_control('card_height',array('label'=>__('Card Height','speaker-profile-manager'),'type'=>\Elementor\Controls_Manager::SLIDER,'size_units'=>array('px'),'range'=>array('px'=>array('min'=>180,'max'=>600)),'default'=>array('unit'=>'px','size'=>330),'tablet_default'=>array('unit'=>'px','size'=>300),'mobile_default'=>array('unit'=>'px','size'=>260),'selectors'=>array('{{WRAPPER}} .spm-sponsor-card'=>'height:{{SIZE}}{{UNIT}};')));
                    $this->add_control('edge_fade',array('label'=>__('Soften Slider Edges','speaker-profile-manager'),'type'=>\Elementor\Controls_Manager::SWITCHER,'default'=>'yes'));
                    $this->add_control('edge_fade_width',array('label'=>__('Edge Fade Width (px)','speaker-profile-manager'),'type'=>\Elementor\Controls_Manager::SLIDER,'range'=>array('px'=>array('min'=>0,'max'=>240)),'default'=>array('unit'=>'px','size'=>70),'selectors'=>array('{{WRAPPER}} .spm-partner-marquee'=>'-webkit-mask-image:linear-gradient(to right,transparent 0,#000 {{SIZE}}px,#000 calc(100% - {{SIZE}}px),transparent 100%);mask-image:linear-gradient(to right,transparent 0,#000 {{SIZE}}px,#000 calc(100% - {{SIZE}}px),transparent 100%);')));
                    $this->add_control('gap',array('label'=>__('Gap (px)','speaker-profile-manager'),'type'=>\Elementor\Controls_Manager::SLIDER,'size_units'=>array('px'),'range'=>array('px'=>array('min'=>0,'max'=>80)),'default'=>array('unit'=>'px','size'=>24),'selectors'=>array('{{WRAPPER}} .spm-marquee-group'=>'gap:{{SIZE}}{{UNIT}};')));
                    $this->add_control('accent',array('label'=>__('Tier Footer Color','speaker-profile-manager'),'type'=>\Elementor\Controls_Manager::COLOR,'default'=>'#62d83e','selectors'=>array('{{WRAPPER}} .spm-partner-tier'=>'background:{{VALUE}};')));
                    $this->end_controls_section();
                }
                protected function render(){spm_render_partner_marquee('spm_sponsor',$this->get_settings_for_display(),true);}
            }
        } else {
            class SPM_Media_Partner_Marquee_Widget extends \Elementor\Widget_Base {
                public function get_name(){return 'spm-media-partner-marquee';}
                public function get_title(){return __('Media Partners Marquee','speaker-profile-manager');}
                public function get_icon(){return 'eicon-gallery-grid';}
                public function get_categories(){return array('general');}
                public function get_style_depends(){return array('spm-marquee');}
                protected function register_controls(){
                    $this->start_controls_section('content',array('label'=>__('Media Partners','speaker-profile-manager')));
                    $this->add_control('limit',array('label'=>__('Number of Partners','speaker-profile-manager'),'type'=>\Elementor\Controls_Manager::NUMBER,'min'=>1,'default'=>16));
                    $this->add_control('speed',array('label'=>__('Duration (seconds)','speaker-profile-manager'),'type'=>\Elementor\Controls_Manager::NUMBER,'min'=>5,'default'=>30));
                    $this->add_control('direction',array('label'=>__('Direction','speaker-profile-manager'),'type'=>\Elementor\Controls_Manager::SELECT,'options'=>array('left'=>__('Right to Left','speaker-profile-manager'),'right'=>__('Left to Right','speaker-profile-manager')),'default'=>'left'));
                    $this->add_control('pause_hover',array('label'=>__('Pause on Hover','speaker-profile-manager'),'type'=>\Elementor\Controls_Manager::SWITCHER,'default'=>'yes'));
                    $this->add_control('logo_width',array('label'=>__('Logo Tile Width (px)','speaker-profile-manager'),'type'=>\Elementor\Controls_Manager::SLIDER,'size_units'=>array('px'),'range'=>array('px'=>array('min'=>100,'max'=>400)),'default'=>array('unit'=>'px','size'=>220),'selectors'=>array('{{WRAPPER}} .spm-media-tile'=>'width:{{SIZE}}{{UNIT}};')));
                    $this->add_responsive_control('tile_height',array('label'=>__('Logo Tile Height','speaker-profile-manager'),'type'=>\Elementor\Controls_Manager::SLIDER,'size_units'=>array('px'),'range'=>array('px'=>array('min'=>60,'max'=>260)),'default'=>array('unit'=>'px','size'=>110),'tablet_default'=>array('unit'=>'px','size'=>100),'mobile_default'=>array('unit'=>'px','size'=>85),'selectors'=>array('{{WRAPPER}} .spm-media-tile'=>'height:{{SIZE}}{{UNIT}};')));
                    $this->add_control('edge_fade',array('label'=>__('Soften Slider Edges','speaker-profile-manager'),'type'=>\Elementor\Controls_Manager::SWITCHER,'default'=>'yes'));
                    $this->add_control('edge_fade_width',array('label'=>__('Edge Fade Width (px)','speaker-profile-manager'),'type'=>\Elementor\Controls_Manager::SLIDER,'range'=>array('px'=>array('min'=>0,'max'=>240)),'default'=>array('unit'=>'px','size'=>70),'selectors'=>array('{{WRAPPER}} .spm-partner-marquee'=>'-webkit-mask-image:linear-gradient(to right,transparent 0,#000 {{SIZE}}px,#000 calc(100% - {{SIZE}}px),transparent 100%);mask-image:linear-gradient(to right,transparent 0,#000 {{SIZE}}px,#000 calc(100% - {{SIZE}}px),transparent 100%);')));
                    $this->add_control('gap',array('label'=>__('Gap (px)','speaker-profile-manager'),'type'=>\Elementor\Controls_Manager::SLIDER,'size_units'=>array('px'),'range'=>array('px'=>array('min'=>0,'max'=>80)),'default'=>array('unit'=>'px','size'=>24),'selectors'=>array('{{WRAPPER}} .spm-marquee-group'=>'gap:{{SIZE}}{{UNIT}};')));
                    $this->end_controls_section();
                }
                protected function render(){spm_render_partner_marquee('spm_media_partner',$this->get_settings_for_display(),false);}
            }
        }
        $widgets_manager->register(new $class());
    }
}
function spm_render_partner_marquee($post_type,$settings,$sponsor) {
    $q=new WP_Query(array('post_type'=>$post_type,'post_status'=>'publish','posts_per_page'=>max(1,absint($settings['limit']??12)),'meta_key'=>'_spm_partner_order','orderby'=>array('meta_value_num'=>'ASC','date'=>'ASC'),'order'=>'ASC','no_found_rows'=>true));
    if (!$q->have_posts()) return;
    $direction=($settings['direction']??'left')==='right'?'reverse':'normal';
    $duration=max(5,absint($settings['speed']??28));
    $pause=($settings['pause_hover']??'yes')==='yes'?' is-pause-hover':'';
    $g1=sanitize_hex_color($settings['tier_gradient_start']??'#36cf2d') ?: '#36cf2d';
    $g2=sanitize_hex_color($settings['tier_gradient_end']??'#82e34b') ?: '#82e34b';
    echo '<div class="spm-marquee spm-partner-marquee'.$pause.'" style="--spm-duration:'.esc_attr($duration).'s;--spm-direction:'.esc_attr($direction).';--spm-tier-start:'.esc_attr($g1).';--spm-tier-end:'.esc_attr($g2).';"><div class="spm-marquee-track">';
    foreach(array(0,1) as $copy) {
        echo '<div class="spm-marquee-group" aria-hidden="'.($copy?'true':'false').'">';
        foreach($q->posts as $item) {
            $logo=absint(get_post_meta($item->ID,'_spm_partner_logo',true)); $title=get_the_title($item->ID); $tier=get_post_meta($item->ID,'_spm_partner_tier',true); $url=get_post_meta($item->ID,'_spm_partner_url',true);
            echo '<div class="spm-marquee-item">';
            $inner='<div class="spm-partner-card '.($sponsor?'spm-sponsor-card':'spm-media-tile').'">';
            if($logo) $inner.='<div class="spm-partner-logo">'.wp_get_attachment_image($logo,'large',false,array('loading'=>'lazy')).'</div>';
            if($sponsor && $tier) $inner.='<div class="spm-partner-tier">'.esc_html($tier).'</div>';
            $inner.='</div>';
            if($url) echo '<a class="spm-partner-link" href="'.esc_url($url).'" target="_blank" rel="noopener noreferrer">'.$inner.'</a>'; else echo $inner;
            echo '</div>';
        }
        echo '</div>';
    }
    echo '</div></div>'; wp_reset_postdata();
}
add_action('elementor/widgets/register','spm_register_partner_widgets');

function spm_register_widget_assets(){
    wp_register_style('spm-marquee',SPM_URL.'assets/marquee.css',array(),SPM_VERSION);
}
add_action('wp_enqueue_scripts','spm_register_widget_assets');

function spm_activate(){spm_register_speaker_cpt();spm_register_partner_cpts();flush_rewrite_rules();}
register_activation_hook(__FILE__,'spm_activate');
register_deactivation_hook(__FILE__,function(){flush_rewrite_rules();});
