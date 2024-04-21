<?php

/**
 *
 * The plugin bootstrap file
 *
 * This file is responsible for starting the plugin using the main plugin class file.
 *
 * @since 0.0.1
 * @package oniros_proyect
 *
 * @wordpress-plugin
 * Plugin Name:     Oniros Tablas 
 * Description:     Tablas de información recopilada
 * Version:         2.1.2
 * Author:          Renan Salazar
 * Author URI:      https://interconexo.com
 * License:         GPL-2.0+
 * License URI:     http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:     oniros-proyect
 * Domain Path:     /lang
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access not permitted.' );
}

if ( ! class_exists( 'oniros_proyect' ) ) {

	/*
	 * main oniros_proyect class
	 *
	 * @class oniros_proyect
	 * @since 0.0.1
	 */
	class oniros_proyect {

		/*
		 * oniros_proyect plugin version
		 *
		 * @var string
		 */
		public $version = '4.7.5';

		/**
		 * The single instance of the class.
		 *
		 * @var oniros_proyect
		 * @since 1.0.0
		 */
		protected static $instance = null;

		/**
		 * Main oniros_proyect instance.
		 *
		 * @since 1.0.0
		 * @static
		 * @return oniros_proyect - main instance.
		 */
		public static function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * oniros_proyect class constructor.
		 */
		public function __construct() {
			$this->load_plugin_textdomain();
			$this->define_constants();
			$this->includes();
            $this->define_form();
            $this->upload_style();
            $this->upload_scripts();
		}

		public function load_plugin_textdomain() {
			load_plugin_textdomain( 'oniros-proyect', false, basename( dirname( __FILE__ ) ) . '/lang/' );
		}

		/**
		 * Include required core files
		 */
		public function includes() {

			// Load custom functions and hooks
			require_once __DIR__ . '/includes/includes.php';
        
		}

		/**
		 * Get the plugin path.
		 *
		 * @return string
		 */
		public function plugin_path() {
			return untrailingslashit( plugin_dir_path( __FILE__ ) );
		}


		/**
		 * Define oniros_proyect constants
		 */
		private function define_constants() {
			define( 'oniros_proyect_PLUGIN_FILE', __FILE__ );
			define( 'oniros_proyect_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
			define( 'oniros_proyect_VERSION', $this->version );
			define( 'oniros_proyect_PATH', $this->plugin_path() );
		}

        
        public function define_form() {
             include_once('proyect/oniros.php');
			 
		}
		
		public function upload_style() {
    		add_action( 'wp_enqueue_scripts', 'load_custom_css_styles_oniros');
            function load_custom_css_styles_oniros() {
                $dir = plugin_dir_url(__FILE__);
                wp_enqueue_style( 'oniros-style',$dir . 'assets/css/style.css', array(), rand(111,9999), 'all');
            }
		}

		public function upload_scripts() {
    		add_action( 'wp_enqueue_scripts', 'load_custom_js_scripts_oniros');
            function load_custom_js_scripts_oniros() {
                $dir = plugin_dir_url(__FILE__);
                if(is_page('home'))
                	wp_enqueue_script( 'oniros-script',$dir . 'assets/js/script.js', array(), rand(111,9999), 'all');

            }
		}
	}

	$oniros_proyect = new oniros_proyect();
}
