<?php

namespace Com\Dungqt3;

defined('ABSPATH') or die('Hello World!');

if (!class_exists('DNT_CPT_SYAP_Shortcode')):

	class DNT_CPT_SYAP_Shortcode {

		private static $instance;
		private $post_type = 'dnt_syap_shortcode';

		public static function get_instance() {
			if (self::$instance == null) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		private function __construct() {
			add_action('init', array($this, 'custom_post_type'), 0);

			//meta boxes
			add_action('add_meta_boxes', array($this, 'add_meta_boxes'));

			// save meta
			add_action('save_post', array($this, 'save_shortcode_settings'));

			//
			add_filter("manage_{$this->post_type}_posts_columns", array($this, 'manage_posts_columns'));
			add_action("manage_{$this->post_type}_posts_custom_column", array($this, 'manage_posts_custom_column'), 10, 2);
		}

		public function manage_posts_custom_column($column_name, $post_id) {
			switch ($column_name) {
				case 'shortcode':
					echo '<code>[dnt_syap_shortcode id="' . $post_id . '"]</code>';
					break;
			}
		}

		public function manage_posts_columns($posts_columns) {
			$new_columns = array();
			foreach ($posts_columns as $key => $label) {
				$new_columns[$key] = $label;
				if ($key == 'title') {
					$new_columns['shortcode'] = 'Shortcode';
				}
			}
			return $new_columns;
		}

		public function save_shortcode_settings($post_id) {
			if (get_post_type($post_id) != $this->post_type) {
				return;
			}

			//
			$dnt_post_types = isset($_POST['dnt_post_types']) ? $_POST['dnt_post_types'] : array();
			$dnt_post_types = is_array($dnt_post_types) ? $dnt_post_types : array();
			update_post_meta($post_id, 'dnt_post_types', $dnt_post_types);
			//var_dump($dnt_post_types);

			// public_key
			$dnt_public_key = wp_generate_password(64, false);
			update_post_meta($post_id, 'dnt_public_key', $dnt_public_key);

			//
			$fields = array('dnt_posts_per_page');
			foreach ($fields as $field) {
				if (!isset($_POST[$field])) {
					continue;
				}
				update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
			}
		}

		public function add_meta_boxes() {
			add_meta_box('dnt_syap_shortcode_settings', 'Shortcode settings', array($this, 'shortcode_settings'), 'dnt_syap_shortcode');
		}

		public function shortcode_settings($post) {
			$post_types = get_post_types(array(), 'objects');
			//var_dump($post_types);
			//var_dump($post->ID);
			?>
			<div class="dnt-fields-wrapper">
				<div class="field">
					<strong style="margin-bottom: 5px; display: block;">Select post types</strong>
					<div>
						<?php
						$dn_post_types = get_post_meta($post->ID, 'dnt_post_types', true);
						$dn_post_types = is_array($dn_post_types) ? $dn_post_types : array();
						//var_dump($dn_post_types);
						foreach ($post_types as $post_type => $object):
							if ($post_type == $this->post_type) {
								continue;
							}
							?>
							<div>
								<input id="dnt_pt_<?php echo $post_type; ?>" type="checkbox" name="dnt_post_types[]" value="<?php echo $post_type; ?>" <?php echo in_array($post_type, $dn_post_types) ? ' checked="checked"' : ''; ?>>
								<label for="dnt_pt_<?php echo $post_type; ?>"><?php echo $object->label; ?></label>
							</div>
						<?php endforeach; ?>
					</div>
				</div>

				<div class="field text-field">
					<?php $dnt_posts_per_page = get_post_meta($post->ID, 'dnt_posts_per_page', true);
					$dnt_posts_per_page = is_int($dnt_posts_per_page) ? $dnt_posts_per_page : 10; ?>
					<label for="dnt_posts_per_page"><strong>Posts per page</strong></label>
					<input type="text" id="dnt_posts_per_page" name="dnt_posts_per_page" value="<?php echo $dnt_posts_per_page; ?>">
				</div>
			</div>

			<style>
				.dnt-fields-wrapper .field {
					margin-bottom: 20px;
				}

				.dnt-fields-wrapper .field.text-field label {
					position: relative;
					top: -3px;
					margin-right: 15px;
				}
			</style>

			<?php if (isset($_GET['action']) && $_GET['action'] == 'edit'): ?>
				<script>
					(function ($) {
						var post_id = parseInt('<?php echo $_GET['post']; ?>');
						$('#titlediv').after('<div style="margin-top: 10px;">Please use this shortcode to show archive posts: <code>[dnt_syap_shortcode id="' + post_id + '"]</code></div>');
					})(jQuery);
				</script>
			<?php endif; ?>

			<?php
		}

		// Register Custom Post Type
		function custom_post_type() {

			$labels = array(
				'name' => _x('Archive Posts Shortcode', 'Post Type General Name', 'text_domain'),
				'singular_name' => _x('Archive Post Shortcode', 'Post Type Singular Name', 'text_domain'),
				'menu_name' => __('Archive Posts Shortcode', 'text_domain'),
				'name_admin_bar' => __('Archive Shortcodes', 'text_domain'),
				'archives' => __('Item Archives', 'text_domain'),
				'attributes' => __('Item Attributes', 'text_domain'),
				'parent_item_colon' => __('Parent Item:', 'text_domain'),
				'all_items' => __('All Items', 'text_domain'),
				'add_new_item' => __('Add New Item', 'text_domain'),
				'add_new' => __('Add New', 'text_domain'),
				'new_item' => __('New Item', 'text_domain'),
				'edit_item' => __('Edit Item', 'text_domain'),
				'update_item' => __('Update Item', 'text_domain'),
				'view_item' => __('View Item', 'text_domain'),
				'view_items' => __('View Items', 'text_domain'),
				'search_items' => __('Search Item', 'text_domain'),
				'not_found' => __('Not found', 'text_domain'),
				'not_found_in_trash' => __('Not found in Trash', 'text_domain'),
				'featured_image' => __('Featured Image', 'text_domain'),
				'set_featured_image' => __('Set featured image', 'text_domain'),
				'remove_featured_image' => __('Remove featured image', 'text_domain'),
				'use_featured_image' => __('Use as featured image', 'text_domain'),
				'insert_into_item' => __('Insert into item', 'text_domain'),
				'uploaded_to_this_item' => __('Uploaded to this item', 'text_domain'),
				'items_list' => __('Items list', 'text_domain'),
				'items_list_navigation' => __('Items list navigation', 'text_domain'),
				'filter_items_list' => __('Filter items list', 'text_domain'),
			);
			$args = array(
				'label' => __('Archive Shortcode', 'text_domain'),
				'description' => __('Archive Shortcodes', 'text_domain'),
				'labels' => $labels,
				'supports' => array('title',),
				'hierarchical' => false,
				'public' => false,
				'show_ui' => true,
				'show_in_menu' => true,
				'menu_position' => 5,
				'show_in_admin_bar' => true,
				'show_in_nav_menus' => true,
				'can_export' => true,
				'has_archive' => false,
				'exclude_from_search' => true,
				'publicly_queryable' => true,
				'capability_type' => 'page',
			);
			register_post_type($this->post_type, $args);

		}

	}

	DNT_CPT_SYAP_Shortcode::get_instance();

endif;