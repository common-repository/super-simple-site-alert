<?php

/** The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 */
class Site_Alert {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 */
	protected $site_alert;

	/**
	 * The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 */
	public function __construct() {
		if ( defined( 'SITE_ALERT_VERSION' ) ) {
			$this->version = SITE_ALERT_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->site_alert = 'site-alert';

		$this->load_dependencies();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Site_Alert_Loader. Orchestrates the hooks of the plugin.
	 * - Site_Alert_Admin. Defines all hooks for the admin area.
	 * - Site_Alert_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-site-alert-loader.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-site-alert-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-site-alert-public.php';

		$this->loader = new Site_Alert_Loader();

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Site_Alert_Admin( $this->get_site_alert(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		//add admin  menu item
		$this->loader->add_action( 'network_admin_menu', $plugin_admin, 'my_admin_menu' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'my_admin_menu' );

		//create custom poste type for alert
		$this->loader->add_action( 'init', $plugin_admin, 'site_alert');

		//add ajax
		$this->loader->add_action( 'wp_ajax_update_site_alert', $plugin_admin, 'update_site_alert');
		$this->loader->add_action( 'wp_ajax_create_group', $plugin_admin, 'create_group');
		$this->loader->add_action( 'wp_ajax_update_group_prop', $plugin_admin, 'update_group_prop');
		$this->loader->add_action( 'wp_ajax_update_group_select_site', $plugin_admin, 'update_group_select_site');
		$this->loader->add_action( 'wp_ajax_poll_group', $plugin_admin, 'poll_group');
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Site_Alert_Public( $this->get_site_alert(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'wp_footer', $plugin_public, 'show_alert' );
		$this->loader->add_shortcode( 'site_alert', $plugin_public, 'site_alert_short_code' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @return    string    The name of the plugin.
	 */
	public function get_site_alert() {
		return $this->site_alert;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @return    Site_Alert_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
