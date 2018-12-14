<?php

class Teeoptimize {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @var     string
	 */
	public $VERSION = '1.1.4';

	/**
	 *
	 * The variable name is used as the text domain when internationalizing strings
	 * of text. Its value should match the Text Domain file header in the main
	 * plugin file.
	 *
	 * @var      string
	 */
	protected $plugin_slug = 'teeoptimize';

	/**
	 * Instance of this class.
	 *
	 * @var      object
	 */

	protected static $instance = null;

	/**
	 * Prefix for custom meta fields
	 *
	 * @var string
	 */
	private $custom_meta_prefix = 'teeoptimize_custom_';

	private $settings_name = 'teeoptimize_settings';
	private static $default_settings = null;

	/**
	 * Initialize the plugin by setting localization and loading public scripts and styles.
	 */
	private function __construct() {
		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
		// Activate plugin when new blog is added
		add_action( 'wpmu_new_blog', array( $this, 'activate_new_site' ) );
		// Load public-facing style sheet and JavaScript.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		// Define custom functionality.
        add_action( 'init', array( $this, 'teeoptimize_post_type' ), 0 );
        add_action( 'template_redirect', array( $this, 'template_redirect' ), 0 );
	}

	private function _get_settings( $settings_key = null ) {
		$defaults = $this->_get_settings_default();
		$settings = get_option( $this->settings_name, $defaults );
		$settings = shortcode_atts( $defaults, $settings );
		return is_null( $settings_key ) ? $settings : ( isset( $settings[$settings_key] ) ? $settings[$settings_key] : false );
	}

	private static function _get_settings_default() {
		if ( is_null( self::$default_settings ) ) {
			self::$default_settings = array(
				'header-code' => '',
				'footer-code' => '',
			);
		}
		return self::$default_settings;
	}

	/**
	 * Return the plugin slug.
	 *
	 * @return   string		Plugin slug variable.
	 */
	public function get_plugin_slug() {
		return $this->plugin_slug;
	}

	/**
	 * Return an instance of this class.
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	/**
	 * Fired when the plugin is activated.
	 *
	 * @param    boolean    $network_wide
	 */
	public static function activate( $network_wide ) {
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			if ( $network_wide  ) {
				// Get all blog ids
				$blog_ids = self::get_blog_ids();
				foreach ( $blog_ids as $blog_id ) {
					switch_to_blog( $blog_id );
					self::single_activate();
				}
				restore_current_blog();
			} else {
				self::single_activate();
			}
		} else {
			self::single_activate();
		}
	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @param    boolean    $network_wide
	 */
	public static function deactivate( $network_wide ) {
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			if ( $network_wide ) {
				// Get all blog ids
				$blog_ids = self::get_blog_ids();
				foreach ( $blog_ids as $blog_id ) {
					switch_to_blog( $blog_id );
					self::single_deactivate();
				}
				restore_current_blog();
			} else {
				self::single_deactivate();
			}
		} else {
			self::single_deactivate();
		}
	}

	/**
	 * Fired when a new site is activated with a WPMU environment.
	 *
	 * @param    int    $blog_id    ID of the new blog.
	 */
	public function activate_new_site( $blog_id ) {
		if ( 1 !== did_action( 'wpmu_new_blog' ) ) {
			return;
		}
		switch_to_blog( $blog_id );
		self::single_activate();
		restore_current_blog();
	}

	/**
	 * Get all blog ids of blogs in the current network that are:
	 * - not archived
	 * - not spam
	 * - not deleted
	 *
	 * @return   array|false    The blog ids, false if no matches.
	 */
	private static function get_blog_ids() {
		/** @var $wpdb WPDB */
		global $wpdb;
		// get an array of blog ids
		$sql = "SELECT blog_id FROM $wpdb->blogs WHERE archived = '0' AND spam = '0' AND deleted = '0'";
		return $wpdb->get_col( $sql );
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 */
	public function load_plugin_textdomain() {
		$domain = $this->plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );
		load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, FALSE, basename( plugin_dir_path( dirname( __FILE__ ) ) ) . '/languages/' );
	}

	/**
	 * Register and enqueue public-facing style sheet.
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_slug . '-plugin-styles', plugins_url( 'assets/css/public.css', __FILE__ ), array(), $this->VERSION );
	}

	/**
	 * Register and enqueues public-facing JavaScript files.
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_slug . '-plugin-script', plugins_url( 'assets/js/public.js', __FILE__ ), array( 'jquery' ), $this->VERSION );
	}

	/**
	 * Fired for each blog when the plugin is activated.
	 */
	private static function single_activate() {
		// @TODO: Define activation functionality here
	}

	/**
	 * Fired for each blog when the plugin is deactivated.
	 */
	private static function single_deactivate() {
		// @TODO: Define deactivation functionality here
	}

	/**
	 * Plugin Actions
	 */
	public function action_method_name() {
		// @TODO: Define your action hook callback here
	}

	/**
	 * Plugin Filters
	 */
	public function filter_method_name() {
		// @TODO: Define your filter hook callback here
	}

	/**
	 * Define custom post type teeoptimize
	 *
	 */
	public function teeoptimize_post_type() {
         $labels = array(
            'name'                => _x( 'Campaigns', 'Post Type General Name', 'teeoptimize_domain' ),
            'singular_name'       => _x( 'Items', 'Post Type Singular Name', 'teeoptimize_domain' ),
            'menu_name'           => __( 'Retargetengine', 'teeoptimize_domain' ),
            'parent_item_colon'   => __( 'Parent Campaign:', 'teeoptimize_domain' ),
            'all_items'           => __( 'All Campaigns', 'teeoptimize_domain' ),
            'view_item'           => __( 'View Campaign', 'teeoptimize_domain' ),
            'add_new_item'        => __( 'Add New Campaign', 'teeoptimize_domain' ),
            'add_new'             => __( 'Add New', 'teeoptimize_domain' ),
            'edit_item'           => __( 'Edit Campaign', 'teeoptimize_domain' ),
            'update_item'         => __( 'Update Campaign', 'teeoptimize_domain' ),
            'search_items'        => __( 'Search Campaign', 'teeoptimize_domain' ),
            'not_found'           => __( 'Not found', 'teeoptimize_domain' ),
            'not_found_in_trash'  => __( 'Not found in Trash', 'teeoptimize_domain' ),
        );
        $args = array(
            'label'               => __( 'teeoptimize', 'teeoptimize_domain' ),
            'description'         => __( 'Campaigns', 'teeoptimize_domain' ),
            'labels'              => $labels,
            'supports'            => array( 'title' ),
            'hierarchical'        => false,
            'public'              => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'show_in_nav_menus'   => true,
            'show_in_admin_bar'   => true,
            'menu_position'       => 5,
            'menu_icon'           => TEEOPTIMIZE_PUBLICURL.'/admin/assets/menu-icon.png',
            'can_export'          => true,
            'has_archive'         => true,
            'exclude_from_search' => true,
            'publicly_queryable'  => true,
            'capability_type'     => 'page',
            'rewrite'             => array('slug' => 'r'),
        );
        register_post_type( 'teeoptimize', $args );
		flush_rewrite_rules();
    }

	/**
	 * Add custom template for campaign page
	 *
	 */
	function template_redirect() {
        if (get_post_type() == 'teeoptimize') {
            global $post;
			$settings = $this->_get_settings();
            $campaign_title = get_the_title();
            $campaign_url = get_post_meta($post->ID, $this->custom_meta_prefix.'campaign_url', true);
            $redirect_url = get_post_meta($post->ID, $this->custom_meta_prefix.'redirect_url', true);
			$header_code = get_post_meta($post->ID, $this->custom_meta_prefix.'header_code', true);
			$footer_code = get_post_meta($post->ID, $this->custom_meta_prefix.'footer_code', true);

			$popup_message = get_post_meta($post->ID, $this->custom_meta_prefix.'popup_message', true);
			if (!empty($popup_message)) {
				$popup_message = json_encode($popup_message);
			} else {
				$popup_message = "";
			}

			$end_date = get_post_meta($post->ID, $this->custom_meta_prefix.'end_date', true);
            if ( !empty($end_date) && strtotime($end_date)-time() > 0 ) {
				$end_date = date('D M d Y H:i:s O', strtotime($end_date));
			} else {
				$end_date = '';
			}
            $background_image = get_post_meta($post->ID, $this->custom_meta_prefix.'image', true);
            $background_image = wp_get_attachment_url($background_image, true);
			//$header_code = $settings['header-code'];
			//$footer_code = $settings['footer-code'];
            include (TEEOPTIMIZE_TEMPLATEPATH . 'single-campaign.php');
            exit;
        }
    }


}
