<?php

/*
Plugin Name: Simple Yearly Archive Posts
Plugin URI:
Description: Simple shortcode for post_type archive posts
Version:     1.0.0
Author:      Dung Nguyen Tien
Author URI:  http://dungqt3.com/
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: dnt_syap
Domain Path: /languages
*/

namespace Com\Dungqt3;

defined('ABSPATH') or die('Hello World!');

if (!class_exists('DNT_Simple_Yearly_Archive_Posts')):

	class DNT_Simple_Yearly_Archive_Posts {

		private static $instance;
		public static $DNT_SYAP_PATH = '';
		public static $DNT_SYAP_URL = '';

		public static function get_instance() {
			if (self::$instance == null) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		private function __construct() {
			//
			self::$DNT_SYAP_PATH = plugin_dir_path(__FILE__);
			self::$DNT_SYAP_URL = plugin_dir_url(__FILE__);

			//
			require_once(self::$DNT_SYAP_PATH . 'inc/cpt-dnt_syap_shortcode.php');
			require_once(self::$DNT_SYAP_PATH . 'inc/shortcode.php');

		}

		private function __clone() {
		}

	}

	DNT_Simple_Yearly_Archive_Posts::get_instance();

endif;