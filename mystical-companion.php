<?php
/**
 * Plugin Name: Mystical Companion
 * Description: A companion plugin for themes by MysticalThemes.
 * Plugin URI:  https://mysticalthemes.com/wordpress-plugins/mystical-companion
 * Version:     1.0.1
 * Author:      bnayawpguy
 * Author URI:  https://mysticalthemes.com/
 * Text Domain: mystical-companion
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/** Some pre define value for easy use **/
define( 'MYSTICAL_VER', '1.0.1' );
define( 'MYSTICAL__FILE__', __FILE__ );
define( 'MYSTICAL_PNAME', basename( dirname(MYSTICAL__FILE__)) );
define( 'MYSTICAL_PBNAME', plugin_basename(MYSTICAL__FILE__) );
define( 'MYSTICAL_PATH', plugin_dir_path( MYSTICAL__FILE__ ) );
define( 'MYSTICAL_MODULES_PATH', MYSTICAL_PATH . 'widgets/' );
define( 'MYSTICAL_URL', plugins_url( '/', MYSTICAL__FILE__ ) );
define( 'MYSTICAL_ASSETS_URL', MYSTICAL_URL . 'assets/' );
define( 'MYSTICAL_VENDORS_URL', MYSTICAL_URL . 'vendors/' );
define( 'MYSTICAL_MODULES_URL', MYSTICAL_URL . 'modules/' );

/**
 * Mystical Companion Class
 *
 * The main class that initiates and runs the plugin.
 *
 * @since 1.0.1
 */
final class Mystical_Companion {

	/**
	 * Plugin Version
	 *
	 * @since 1.0.1
	 *
	 * @var string The plugin version.
	 */
	const VERSION = '1.0.1';

	/**
	 * Minimum Elementor Version
	 *
	 * @since 1.0.1
	 *
	 * @var string Minimum Elementor version required to run the plugin.
	 */
	const MINIMUM_ELEMENTOR_VERSION = '2.0.0';

	/**
	 * Minimum PHP Version
	 *
	 * @since 1.0.1
	 *
	 * @var string Minimum PHP version required to run the plugin.
	 */
	const MINIMUM_PHP_VERSION = '7.0';

	/**
	 * Instance
	 *
	 * @since 1.0.1
	 *
	 * @access private
	 * @static
	 *
	 * @var Mystical_Companion The single instance of the class.
	 */
	private static $_instance = null;

	/**
	 * Instance
	 *
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @since 1.0.1
	 *
	 * @access public
	 * @static
	 *
	 * @return Mystical_Companion An instance of the class.
	 */
	public static function instance() {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;

	}

	/**
	 * Constructor
	 *
	 * @since 1.0.1
	 *
	 * @access public
	 */
	public function __construct() {

		add_action( 'init', [ $this, 'i18n' ] );
		add_action( 'plugins_loaded', [ $this, 'init' ] );

	}

	/**
	 * Load Textdomain
	 *
	 * Load plugin localization files.
	 *
	 * Fired by `init` action hook.
	 *
	 * @since 1.0.1
	 *
	 * @access public
	 */
	public function i18n() {

		load_plugin_textdomain( 'mystical-companion' );

	}

	/**
	 * Initialize the plugin
	 *
	 * Load the plugin only after Elementor (and other plugins) are loaded.
	 * Checks for basic plugin requirements, if one check fail don't continue,
	 * if all check have passed load the files required to run the plugin.
	 *
	 * Fired by `plugins_loaded` action hook.
	 *
	 * @since 1.0.1
	 *
	 * @access public
	 */
	public function init() {

		/** Add Image Sizes **/
		add_image_size( 'mystical-room-carousel', 640, 700, true );
		add_image_size( 'mystical-blogs-carousel', 400, 280, true );
		add_image_size( 'mystical-testimonial-carousel', 80, 80, true );

		/** WP Hotel Booking Extra Options Meta **/
		add_action('add_meta_boxes', [ $this, 'add_metabox' ] );
		add_action('save_post', [ $this, 'save_eo_icon_cb' ] );

		/** Enqueue Styles and Scripts in Post Page only **/
		add_action('admin_print_styles-post.php', [ $this, 'add_metabox_scripts' ] );
		add_action('admin_print_styles-post-new.php', [ $this, 'add_metabox_scripts' ] );

		// Check if Elementor installed and activated
		if ( ! did_action( 'elementor/loaded' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_missing_main_plugin' ] );
			return;
		}

		// Check for required Elementor version
		if ( ! version_compare( ELEMENTOR_VERSION, self::MINIMUM_ELEMENTOR_VERSION, '>=' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_minimum_elementor_version' ] );
			return;
		}

		// Check for required PHP version
		if ( version_compare( PHP_VERSION, self::MINIMUM_PHP_VERSION, '<' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_minimum_php_version' ] );
			return;
		}

		/** Include Helper File **/
		require_once( __DIR__ . '/inc/helper.php' );

		/** Register Widget Styles **/
		add_action( 'elementor/frontend/before_register_styles', [ $this, 'enqueue_widget_styles' ] );
		add_action( 'elementor/frontend/before_register_scripts', [ $this, 'enqueue_site_scripts' ] );

		/** Add Widget Categories **/
		add_action( 'elementor/elements/categories_registered', [ $this, 'add_elementor_widget_categories' ] );

		/** Add Widgets **/
		add_action( 'elementor/widgets/widgets_registered', [ $this, 'init_widgets' ] );

		/** Add Control **/
		add_action( 'elementor/controls/controls_registered', [ $this, 'init_controls' ] );
	}

	/** Enqueue Metabox Scripts **/
	function add_metabox_scripts() {
		/** Lineicons Styles **/
		wp_enqueue_style( 'lineicons', MYSTICAL_VENDORS_URL . 'line-icons/LineIcons.min.css' );

		/** Custom Styles **/
		wp_enqueue_style( 'mystical-companion-styles', MYSTICAL_ASSETS_URL . 'css/custom-styles.css' );

		/** Mystical Companion Custom Scripts **/
		wp_enqueue_script( 'mystical-companion-scripts', MYSTICAL_ASSETS_URL . 'js/custom-scripts.js', array('jquery') );
	}

	/** Add Extra Option Metabox **/
	public function add_metabox() {
		add_meta_box(
			'mystical_eo_icon',
			esc_html__( 'Feature Icon', 'mystical-companion' ),
			array( $this, 'eo_icon_cb' ),
			'hb_extra_room',
			'side',
			'high'
		);
	}

	/** mystical_eo_icon_cb Callback function **/
	public function eo_icon_cb() {
		global $post;

		$lineicons = array (
			'lni-bi-cycle',
			'lni-dinner',
			'lni-signal',
			'lni-surfboard',
			'lni-wheelchair',
			'lni-calendar',
			'lni-apartment',
			'lni-island',
			'lni-service',
			'lni-gift',
		);

		wp_nonce_field( basename( __FILE__ ), 'mystical_eo_icon_nonce' );
		$mystical_eo_icon = get_post_meta( $post->ID, 'mystical_eo_icon', true );
		?>
		<div class="mystical_extra_feat_icon">
			<?php if( !empty( $lineicons ) ) : ?>
				<ul class="mystical_eo_iconlist">
					<?php foreach( $lineicons as $icon ) : ?>
						<?php $class = ( $mystical_eo_icon == $icon ) ? 'active' : ''; ?>
						<li class="<?php echo esc_attr($class); ?>">
							<span class="<?php echo esc_attr( $icon ); ?>" ></span>
						</li>
					<?php endforeach; ?>
				</ul>
				<input name="mystical_eo_icon" id="mystical_eo_icon" type="hidden" value="" />
			<?php endif; ?>
		</div>
		<?php
	}

	/** Mystical Save Extra Option feature icons **/
	public function save_eo_icon_cb( $post_id ) {
	    global $post; 
	    // Verify the nonce before proceeding.
	    if ( !isset( $_POST[ 'mystical_eo_icon_nonce' ] ) || !wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ 'mystical_eo_icon_nonce' ] ) ), basename( __FILE__ ) ) ) {
	        return;
	    }

	    // Stop WP from clearing custom fields on autosave
	    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE){
	        return;
	    }

	    if ( isset( $_POST['post_type'] ) && 'hb_extra_room' == $_POST['post_type']) {  
	        if (!current_user_can( 'edit_page', $post_id ) )  
	        return $post_id;  
	    }

	    $mystical_eo_icon = isset( $_POST['mystical_eo_icon'] ) ? sanitize_text_field( wp_unslash($_POST['mystical_eo_icon']) ) : '';

    	update_post_meta($post_id, 'mystical_eo_icon', $mystical_eo_icon);  
	}

	/**
	 * Admin notice
	 *
	 * Warning when the site doesn't have Elementor installed or activated.
	 *
	 * @since 1.0.1
	 *
	 * @access public
	 */
	public function admin_notice_missing_main_plugin() {

		if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );

		$message = sprintf(
			/* translators: 1: Plugin name 2: Elementor */
			esc_html__( '"%1$s" requires "%2$s" to be installed and activated.', 'mystical-companion' ),
			'<strong>' . esc_html__( 'Mystical Companion', 'mystical-companion' ) . '</strong>',
			'<strong>' . esc_html__( 'Elementor', 'mystical-companion' ) . '</strong>'
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );

	}

	/**
	 * Admin notice
	 *
	 * Warning when the site doesn't have a minimum required Elementor version.
	 *
	 * @since 1.0.1
	 *
	 * @access public
	 */
	public function admin_notice_minimum_elementor_version() {

		if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );

		$message = sprintf(
			/* translators: 1: Plugin name 2: Elementor 3: Required Elementor version */
			esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'mystical-companion' ),
			'<strong>' . esc_html__( 'Elementor Test Extension', 'mystical-companion' ) . '</strong>',
			'<strong>' . esc_html__( 'Elementor', 'mystical-companion' ) . '</strong>',
			 self::MINIMUM_ELEMENTOR_VERSION
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );

	}

	/**
	 * Admin notice
	 *
	 * Warning when the site doesn't have a minimum required PHP version.
	 *
	 * @since 1.0.1
	 *
	 * @access public
	 */
	public function admin_notice_minimum_php_version() {

		if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );

		$message = sprintf(
			/* translators: 1: Plugin name 2: PHP 3: Required PHP version */
			esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'mystical-companion' ),
			'<strong>' . esc_html__( 'Elementor Test Extension', 'mystical-companion' ) . '</strong>',
			'<strong>' . esc_html__( 'PHP', 'mystical-companion' ) . '</strong>',
			 self::MINIMUM_PHP_VERSION
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );

	}

	/** Enqueue Widget Styles **/
	public function enqueue_widget_styles() {

		/** Vendor Styles **/
		wp_enqueue_style( 'owl-carousel', MYSTICAL_VENDORS_URL . 'owl-carousel/owl.carousel.min.css' ); // Owl Carousel

		/** Custom Styles **/
		wp_enqueue_style( 'mystical-companion', MYSTICAL_ASSETS_URL . 'css/mystical-companion.css' );

	}

	/** Enqueue Widget Scripts **/
	public function enqueue_site_scripts() {

		/** Vendor Scripts **/
		wp_register_script( 'owl-carousel',  MYSTICAL_VENDORS_URL . 'owl-carousel/owl.carousel.min.js', [ 'jquery' ] ); // Owl Carousel

	}

	/** Register Widget Category **/
	public function add_elementor_widget_categories( $elements_manager ) {

		$elements_manager->add_category(
			'mystical-elements',
			[
				'title' => esc_html__( 'Mystical Elements', 'mystical-companion' ),
				'icon' => 'fa fa-plug',
			]
		);

	}

	/**
	 * Init Widgets
	 *
	 * Include widgets files and register them
	 *
	 * @since 1.0.1
	 *
	 * @access public
	 */
	public function init_widgets() {

		// Include Widget files
		require_once( __DIR__ . '/widgets/mystical-counter-widget.php' );
		require_once( __DIR__ . '/widgets/mystical-rooms-carousel-widget.php' );
		require_once( __DIR__ . '/widgets/mystical-testimonial-carousel-widget.php' );
		require_once( __DIR__ . '/widgets/mystical-blogs-carousel-widget.php' );

		// Register widget
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Mystical_Counter_Widget() );
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Mystical_Rooms_Carousel_Widget() );
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Mystical_Testimonial_Carousel_Widget() );
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Mystical_Blogs_Carousel_Widget() );

	}

	/**
	 * Init Controls
	 *
	 * Include controls files and register them
	 *
	 * @since 1.0.1
	 *
	 * @access public
	 */
	public function init_controls() {

		// Include Control files
		require_once( __DIR__ . '/controls/mystical-icons-control.php' );

		// Register control
		\Elementor\Plugin::$instance->controls_manager->register_control( 'mysticalicon', new \Mystical_Icons_Control() );

	}

}

Mystical_Companion::instance();