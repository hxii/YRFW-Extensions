<?php

class YRFW_Debug_Page {

	public function __construct()
	{
		add_action( 'init', array( $this, 'get_debug_info' ) );
	}

	protected $extension_meta = [
		'extension_name'        => 'Debug Page',
		'extension_description' => 'This extension allows support to access basic debug information about the site using a hashed key',
		'extension_version'     => '0.1',
		'extension_author'      => 'Paul Glushak',
	];

	public function register_extension() {
		$extension_handler = YRFW_Extensions::get_instance();
		$extension_handler->register_extension( $this->extension_meta );
	}

	/**
	 * Dump debug info when accessing `yotpo_debug_setting` arguemt with proper key
	 *
	 * @return void
	 */
	public function get_debug_info() {
		if ( isset( $_GET['yotpo_debug_settings'] ) ) {
			global $settings_instance, $yotpo_scheduler;
			$arg   = $_GET['yotpo_debug_settings'];
			$token = get_transient( 'yotpo_utoken' );
			$hmac  = hash_hmac( 'sha1', $settings_instance['app_key'], $token );
			if ( $hmac === $arg ) {
				global $yrfw_logger;
				$debug_info_array = [
					'Yotpo Plugin Version' => YRFW_PLUGIN_VERSION,
					'WooCommerce Version'  => WOOCOMMERCE_VERSION,
					'WordPress Version'    => get_bloginfo( 'version' ),
					'PHP Version'          => phpversion(),
					'Logger Version'       => Hxii_Logger::get_version(),
					'Logger Level'         => $yrfw_logger->loglevel,
					'Logger File'          => YRFW_PLUGIN_URL . '/' . $yrfw_logger->get_filename( true ),
					'Widget Version'       => ( $transient = get_transient( 'yotpo_widget_version' ) ) ? $transient : 'null',
					'Scheduled Submission' => ( $sched     = $yotpo_scheduler->get_scheduler() ) ? $sched : 'null',
					'Products Cache'       => YRFW_PLUGIN_URL . '/products.json',
					'Settings File'        => YRFW_PLUGIN_URL . '/settings.json',
					'Last Submitted Order' => ( get_transient( 'yotpo_last_sent_order' ) ),
				];
				die( json_encode( $debug_info_array, JSON_PRETTY_PRINT ) );
			}
		}
	}

}