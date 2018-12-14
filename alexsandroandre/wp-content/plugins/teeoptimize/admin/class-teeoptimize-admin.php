<?php

/**
 * Plugin class. This class should ideally be used to work with the
 * administrative side of the WordPress site.
 *
 * If you're interested in introducing public-facing
 * functionality, then refer to `class-teeoptimize.php`
 *
 */
class Teeoptimize_Admin {

	protected static $instance = null;
	protected $plugin_screen_hook_suffix = null;
	private static $default_settings = null;
	private $custom_meta_prefix = 'teeoptimize_custom_';
	private $custom_meta_fields = array();
	public $version = '1.1.4';

	private $settings_name = 'teeoptimize_settings';

	private function __construct() {
		/**
		 * Call $plugin_slug from public plugin class.
		 */
		$plugin            = Teeoptimize::get_instance();
		$this->plugin_slug = $plugin->get_plugin_slug();
		// Load admin style sheet and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
		// Add the options page and menu item.
		// add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );
		// Add an action link pointing to the options page.
		$plugin_basename = plugin_basename( plugin_dir_path( __DIR__ ) . $this->plugin_slug . '.php' );
		add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );
		// Register Settins
		add_action('admin_init', array($this, 'register_settings'));
		//add_action('admin_menu', array($this, 'add_settings_page'));
		//Define custom functionality.
		add_action( 'init', array( $this, 'plugin_init' ), 0 );
		add_action( 'admin_head', array( $this, 'add_custom_scripts' ), 0 );
		add_action( 'add_meta_boxes', array( $this, 'add_custom_meta_box' ), 0 );
		add_action( 'save_post', array( $this, 'save_custom_meta' ), 0 );
	}

	public function plugin_init() {
		$this->custom_meta_fields = array(
			array(
				'label' => 'Campaign URL *',
				'desc'  => 'This field is required.',
				'id'    => $this->custom_meta_prefix . 'campaign_url',
				'type'  => 'text',
				'class' => 'required'
			),
			array(
				'label' => 'Retargeting pixel, Facebook meta data, and google analytics',
				'desc'  => '',
				'id'    => $this->custom_meta_prefix . 'header_code',
				'type'  => 'textarea',
				'class' => 'header_code'
			),
			array(
				'label' => 'Footer Tracking Code',
				'desc'  => '',
				'id'    => $this->custom_meta_prefix . 'footer_code',
				'type'  => 'textarea',
				'class' => 'footer_code'
			),
		);
	}

	public function add_custom_scripts() {
		$screen = get_current_screen();
		if ( $screen->id == 'teeoptimize' ) {
			$output = '<script type="text/javascript">
                jQuery(function() {';
			foreach ( $this->custom_meta_fields as $field ) { // loop through the fields looking for certain types
				if ( $field['type'] == 'date' ) {
					$output .= 'jQuery(".datepicker").datepicker({ dateFormat: "yy-mm-dd" });';
				}
			}
			$output .= '});
            </script>';
			echo $output;
		}
	}

	/**
	 * Add plugin meta box
	 */
	public function add_custom_meta_box() {
		add_meta_box(
			'custom_meta_box', // $id
			'Campaign Details', // $title
			array( $this, 'show_custom_meta_box' ), // $callback
			'teeoptimize', // $page
			'normal', // $context
			'high' ); // $priority
	}

	public function show_custom_meta_box() {
		global $post;
		// Use nonce for verification
		echo '<input type="hidden" name="custom_meta_box_nonce" value="' . wp_create_nonce( basename( __FILE__ ) ) . '" />';
		// Begin the field table and loop
		echo '<table class="form-table">';
		foreach ( $this->custom_meta_fields as $field ) {
			// get value of this field if it exists for this post
			$meta = get_post_meta( $post->ID, $field['id'], true );
			// begin a table row with
			echo '<tr><th><label for="' . $field['id'] . '">' . $field['label'] . '</label></th><td>';
			switch ( $field['type'] ) {
				// text
				case 'text':
					echo '<input class="' . $field['class'] . '" type="text" name="' . $field['id'] . '" id="' . $field['id'] . '" value="' . $meta . '" size="60" />
                        <br /><span class="description">' . $field['desc'] . '</span>';
					break;
				// textarea
				case 'textarea':
					echo '<textarea name="' . $field['id'] . '" id="' . $field['id'] . '" cols="60" rows="4">' . $meta . '</textarea>
                        <br /><span class="description">' . $field['desc'] . '</span>';
					break;
				// checkbox
				case 'checkbox':
					echo '<input type="checkbox" name="' . $field['id'] . '" id="' . $field['id'] . '" ', $meta ? ' checked="checked"' : '', '/>
                        <label for="' . $field['id'] . '">' . $field['desc'] . '</label>';
					break;
				// select
				case 'select':
					echo '<select name="' . $field['id'] . '" id="' . $field['id'] . '">';
					foreach ( $field['options'] as $option ) {
						echo '<option', $meta == $option['value'] ? ' selected="selected"' : '', ' value="' . $option['value'] . '">' . $option['label'] . '</option>';
					}
					echo '</select><br /><span class="description">' . $field['desc'] . '</span>';
					break;
				// date
				case 'date':
					echo '<input type="text" class="datepicker" name="' . $field['id'] . '" id="' . $field['id'] . '" value="' . $meta . '" size="30" />
			            <br /><span class="description">' . $field['desc'] . '</span>';
					break;
				// image
				case 'image':
					$image = get_template_directory_uri() . '/images/image.png';
					echo '<span class="custom_default_image" style="display:none">' . $image . '</span>';
					if ( $meta ) {
						$image = wp_get_attachment_image_src( $meta, 'medium' );
						$image = $image[0];
					}
					echo '<input name="' . $field['id'] . '" type="hidden" class="custom_upload_image" value="' . $meta . '" />
                        <img src="' . $image . '" class="custom_preview_image" alt="" /><br />
                    <input class="custom_upload_image_button button" type="button" value="Choose Image" />
                    <small> <a href="#" class="custom_clear_image_button">Remove Image</a></small>';
					break;
			}
			echo '</td></tr>';
		}
		echo '</table>';
	}

	/**
	 * Save the custom meta
	 *
	 * @param $post_id
	 *
	 * @return mixed
	 */
	public function save_custom_meta( $post_id ) {
		if ( isset( $_POST['custom_meta_box_nonce'] ) ) {
			$nonce = $_POST['custom_meta_box_nonce'];
		} else {
			$nonce = false;
		}
		if ( ! wp_verify_nonce( $nonce, basename( __FILE__ ) ) ) {
			return $post_id;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}
		if ( 'page' == $_POST['post_type'] ) {
			if ( ! current_user_can( 'edit_page', $post_id ) ) {
				return $post_id;
			}
		} elseif ( ! current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}
		foreach ( $this->custom_meta_fields as $field ) {
			$old = get_post_meta( $post_id, $field['id'], true );
			$new = $_POST[$field['id']];
			if ( $new && $new != $old ) {
				update_post_meta( $post_id, $field['id'], $new );
			} elseif ( '' == $new && $old ) {
				delete_post_meta( $post_id, $field['id'], $old );
			}
		}
	}

	public function register_settings() {
		register_setting( $this->settings_name, $this->settings_name, array( $this, 'sanitize_settings' ) );
	}

	public static function sanitize_settings( $settings ) {
		$defaults = self::_get_settings_default();

		return shortcode_atts( $defaults, $settings );
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

	private function _settings_id( $key, $echo = true ) {
		$settings_name = $this->settings_name;
		$id            = "{$settings_name}-{$key}";
		if ( $echo ) {
			echo $id;
		}

		return $id;
	}

	private function _settings_name( $key, $echo = true ) {
		$settings_name = $this->settings_name;
		$name          = "{$settings_name}[{$key}]";
		if ( $echo ) {
			echo $name;
		}

		return $name;
	}

	/// Template tags
	public static function get_template_tag() {
		return '';
	}

	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Register and enqueue admin-specific style sheet.
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_styles() {
		$screen = get_current_screen();
		if ( $this->plugin_screen_hook_suffix == $screen->id ) {
			wp_enqueue_style( $this->plugin_slug . '-admin-styles', plugins_url( 'assets/css/admin.css', __FILE__ ), array( 'wp-color-picker' ), $this->version );
		}
		if ( $screen->id == 'teeoptimize' ) {
			wp_enqueue_style( 'thickbox' );
			wp_enqueue_style( $this->plugin_slug . '-admin-styles', plugins_url( 'assets/css/admin.css', __FILE__ ) );
			wp_enqueue_style( 'jquery-ui-custom', plugins_url( 'assets/css/jquery-ui-custom.css', __FILE__ ) );
		}
	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_scripts() {
		$screen = get_current_screen();
		if ( $this->plugin_screen_hook_suffix == $screen->id ) {
			wp_enqueue_script( $this->plugin_slug . '-admin-script', plugins_url( 'assets/js/admin.js', __FILE__ ), array( 'jquery', 'wp-color-picker' ), $this->version );
		}
		if ( $screen->id == 'teeoptimize' ) {
			wp_enqueue_script( 'thickbox' );
			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_enqueue_script( $this->plugin_slug . '-form-validate', plugins_url( 'assets/js/jquery.validate.min.js', __FILE__ ), array( 'jquery' ), $this->version );
			wp_enqueue_script( $this->plugin_slug . '-admin-script', plugins_url( 'assets/js/admin.js', __FILE__ ), array( 'jquery', 'wp-color-picker' ), $this->version );
		}
	}

	private function _get_settings( $settings_key = null ) {
		$defaults = self::_get_settings_default();
		$settings = get_option( $this->settings_name, $defaults );
		$settings = shortcode_atts( $defaults, $settings );
		return is_null( $settings_key ) ? $settings : ( isset( $settings[$settings_key] ) ? $settings[$settings_key] : false );
	}

	/**
	 * Add settings action link to the plugins page.
	 */
	public function add_action_links( $links ) {
		return ( $links );
		/*
		return array_merge(
			array(
				'settings' => '<a href="' . admin_url( 'options-general.php?page=' . $this->plugin_slug ) . '">' . __( 'Settings', $this->plugin_slug ) . '</a>'
			),
			$links
		);
		*/
	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 */
	public function add_plugin_admin_menu() {

		/**
		 * Add a settings page for this plugin to the Settings menu.
		 *
		 */
		$this->plugin_screen_hook_suffix = add_options_page(
			__( 'Teeoptimize Settings', $this->plugin_slug ),
			__( 'Teeoptimize', $this->plugin_slug ),
			'manage_options',
			$this->plugin_slug,
			array( $this, 'display_plugin_admin_page' )
		);
	}
	/**
	 * Render the admin page for this plugin.
	 */
	public function display_plugin_admin_page() {
		$settings = self::_get_settings();
		include_once( 'views/admin.php' );
	}


}
