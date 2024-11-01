<?php

namespace Com\Dungqt3;

defined('ABSPATH') or die('Hello World!');

if (!class_exists('DNT_SYAP_Shortcode')):

	class DNT_SYAP_Shortcode {

		private static $instance;
		private $default_posts_per_page = 2;

		public static function get_instance() {
			if (self::$instance == null) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		private function __construct() {
			add_shortcode('dnt_syap_shortcode', array($this, 'dnt_syap_shortcode'));

			// styles & scripts
			add_action('wp_enqueue_scripts', array($this, 'wp_enqueue_scripts'));

			// define year in get_archives_list
			add_filter('get_archives_link', array($this, 'get_archives_link'), 11, 6);

			// ajax
			add_action('wp_ajax_dnt_syap_load_posts', array($this, 'dnt_syap_load_posts'));
			add_action('wp_ajax_nopriv_dnt_syap_load_posts', array($this, 'dnt_syap_load_posts'));
		}

		public function dnt_syap_load_posts() {
			$response = array(
				'success' => false,
				'message' => 'unknown',
			);

			if (!$this->validate_atts($_POST)) {
				echo json_encode($response);
				wp_die();
				return;
			}

			//
			$query_args = $this->prepare_query_args($_POST);

			//
			$query = new \WP_Query($query_args);
			if ($query->have_posts()) {
				$max_page = $query->max_num_pages;
				ob_start();
				$this->render_posts_list($query);
				$content = ob_get_clean();
				$response = array(
					'success' => true,
					'content' => $content,
					'max_page' => $max_page,
				);
			} else {
				$response['message'] = 'no more posts';
			}
			echo json_encode($response);
			wp_die();
		}

		public function get_archives_link($link_html, $url, $text, $format, $before, $after) {
			$link_html = str_replace('dnt-year', sanitize_title($text), $link_html);
			return $link_html;
		}

		public function wp_enqueue_scripts() {
			// css
			wp_enqueue_style('dnt-syap-main-css', DNT_Simple_Yearly_Archive_Posts::$DNT_SYAP_URL . 'assets/css/main.css');

			// js
			//wp_enqueue_script('dnt-syap-main-js', DNT_Simple_Yearly_Archive_Posts::$DNT_SYAP_URL . 'assets/js/main.js', array('jquery'));
			wp_enqueue_script('dnt-syap-main-js', DNT_Simple_Yearly_Archive_Posts::$DNT_SYAP_URL . 'assets/js/main.min.js', array('jquery'));

			// ajaxurl
			wp_localize_script('dnt-syap-main-js', 'my_ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
		}

		public function dnt_syap_shortcode($atts, $content = null) {
			if (!isset($atts['id'])) {
				return null;
			}

			if (get_post_type($atts['id']) != 'dnt_syap_shortcode') {
				return null;
			}
			$atts['dnt_public_key'] = get_post_meta($atts['id'], 'dnt_public_key', true);
			$atts['posts_per_page'] = get_post_meta($atts['id'], 'dnt_posts_per_page', true);

			// prepare query_args
			$query_args = $this->prepare_query_args($atts);

			// render content
			if (!$query_args) {
				return null;
			}

			//
			ob_start();

			//
			$query = new \WP_Query($query_args);
			if ($query->have_posts()):
				$max_page = $query->max_num_pages;
				?>

				<div class="dnt-archive-posts" data-current-year-filter="<?php echo $query_args['dnt_year']; ?>">
					<div class="archive-posts-nav">
						<ul>
							<?php wp_get_archives(array('type' => 'yearly', 'format' => 'custom', 'before' => '<li data-year="dnt-year">', 'after' => '</li>')); ?>
						</ul>
					</div>
					<div class="archive-posts-content">
						<div class="posts-list">
							<?php $this->render_posts_list($query); ?>
						</div>
						<div class="posts-pagination">
							<?php $available_fields = array('id', 'dnt_public_key', 'posts_per_page', 'dnt_year', 'dnt_page');
							foreach ($available_fields as $field):
								if (!isset($query_args[$field])) {
									continue;
								} ?>
								<input type="hidden" name="<?php echo $field; ?>" value="<?php echo sanitize_text_field($query_args[$field]); ?>">
							<?php endforeach; ?>

							<?php for ($page_index = 1; $page_index <= $max_page; $page_index++): ?>
								<a href="javascript:void(0);" class="<?php echo $page_index == $query_args['dnt_page'] ? 'current' : ''; ?>" data-page="<?php echo $page_index; ?>"><?php echo $page_index; ?></a>
							<?php endfor; ?>
						</div>
					</div>
				</div>

				<?php
			endif;

			//
			$content = ob_get_clean();
			return $content;
		}

		public function render_posts_list(\WP_Query $query) {
			while ($query->have_posts()):
				$query->the_post(); ?>
				<div class="post-wrapper">
					<h3 class="title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
					<p class="date">
						<time datetime="<?php echo get_the_date('Y-m-d H:i'); ?>"><?php echo get_the_date(); ?></time>
					</p>
					<div class="excerpt"><?php the_excerpt(); ?></div>
				</div>
				<?php
			endwhile;
		}

		private function validate_atts($atts) {
			if (!isset($atts['id']) || !isset($atts['dnt_public_key'])) {
				return false;
			}

			if (get_post_type($atts['id']) != 'dnt_syap_shortcode') {
				return false;
			}

			$dnt_public_key = get_post_meta($atts['id'], 'dnt_public_key', true);
			if ($atts['dnt_public_key'] != $dnt_public_key) {
				return false;
			}

			//
			return true;
		}

		private function prepare_query_args($atts) {
			$post_types = get_post_meta($atts['id'], 'dnt_post_types', true);
			if (!is_array($post_types) || sizeof($post_types) < 1) {
				return null;
			}

			//
			$posts_per_page = isset($atts['posts_per_page']) ? $atts['posts_per_page'] : $this->default_posts_per_page;
			$posts_per_page = is_numeric($posts_per_page) ? intval($posts_per_page) : $this->default_posts_per_page;

			// default
			$query_args = array(
				'post_type' => $post_types,
				'posts_per_page' => $posts_per_page,
				'post_status' => 'publish',
			);

			//
			$query_args['id'] = $atts['id'];
			$query_args['dnt_public_key'] = $atts['dnt_public_key'];

			//
			$dnt_year = 0;
			if (!isset($atts['dnt_year'])) {
				$latest_post = wp_get_recent_posts(array('numberposts' => 1), OBJECT);
				$latest_post = is_array($latest_post) && sizeof($latest_post) > 0 ? $latest_post[0] : false;

				//
				if (!$latest_post) {
					return null;
				}

				//
				$dnt_year = date('Y', strtotime($latest_post->post_date));
			} else {
				$dnt_year = $atts['dnt_year'];
			}

			//
			$next_year = intval($dnt_year) + 1;
			$query_args['date_query'] = array(
				array(
					'year' => $dnt_year,
					'compare' => '>='
				), array(
					'year' => $next_year,
					'compare' => '<'
				)
			);

			// for style
			$query_args['dnt_year'] = $dnt_year;

			//
			$query_args['dnt_page'] = isset($atts['dnt_page']) ? $atts['dnt_page'] : 1;
			$query_args['offset'] = $posts_per_page * ($query_args['dnt_page'] - 1);

			//
			return $query_args;
		}

	}

	DNT_SYAP_Shortcode::get_instance();

endif;