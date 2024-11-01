<?php

/** The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 */
class Site_Alert_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @access   private
	 * @var      string    $site_alert    The ID of this plugin.
	 */
	private $site_alert;

	/**
	 * The version of this plugin.
	 *
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param      string    $site_alert       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $site_alert, $version ) {

		$this->site_alert = $site_alert;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 */
	public function enqueue_styles() {
		wp_enqueue_style('dashicons');
		wp_enqueue_style( 'sso-admin', plugin_dir_url( __FILE__ ) . 'css/site-alert-public.css', array(), $this->version, 'all' );
		wp_enqueue_style( 'sssa-bootstrap-css', plugin_dir_url(__DIR__) . 'admin/js/bootstrap-5.1.3/css/bootstrap.min.css', array(), $this->version, false );
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( 'sssa-site-alert', plugin_dir_url( __FILE__ ) . 'js/site-alert-public.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( 'sssa-bootstrap-js', plugin_dir_url(__DIR__) . 'admin/js/bootstrap-5.1.3/js/bootstrap.bundle.min.js', array(), $this->version, false );
		wp_enqueue_script( 'sssa-utilities-js', plugin_dir_url(__DIR__) . 'admin/js/utilities.js', array(), $this->version, false );
	}

	public function show_alert() {
		//suppress the alert from showing up in Elementor editor
		require_once plugin_dir_path(__DIR__) . '/includes/utilities.php';
		if ( class_exists('Elementor\Plugin') ) {
			if ( \Elementor\Plugin::$instance->preview->is_preview_mode() ) {
			} else {
				require_once 'partials/site-alert-public-display.php';
			}
		}else{
			require_once 'partials/site-alert-public-display.php';
		}
	}

	public function site_alert_short_code( $atts ) {
		if ( class_exists('Elementor\Plugin') ) {
			if ( \Elementor\Plugin::$instance->preview->is_preview_mode() ) {
			}else{
	    	return "<div class='site-alert-anchor'></div>";
			}
		}
	}

}
