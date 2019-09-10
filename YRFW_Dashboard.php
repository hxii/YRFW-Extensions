<?php

class YRFW_Dashboard_Test {

	protected $api;

	protected $settings;

	protected $extension_meta = [
		'extension_name'        => 'Dashboard',
		'extension_description' => 'Your Yotpo stats in a glance',
		'extension_version'     => '0.1',
		'extension_author'      => 'Paul Glushak',
	];

	protected $css = '
	<style>
		#yotpo_dashboard_widget {
			background: linear-gradient( to bottom, #fff, #f2fdff );
		}
		ul.yotpo-metrics {
			display: flex;
			flex-flow: row wrap;
		}
		ul.yotpo-metrics li {
			flex: 0 1 calc(33% - 20px);
			margin: 5px 5px;
			padding: 5px;
			display: flex;
			flex-direction: column;
			text-align: center;
		}
		ul.yotpo-metrics .metrics-value {
			font-size: 2em;
		}
		ul.yotpo-metrics .metrics-key {
			text-transform: uppercase;
			font-size: .85em;
			font-weight: bold;
		}
	</style>
	';

	public function register_extension() {
		$extension_handler = YRFW_Extensions::get_instance();
		$extension_handler->register_extension( $this->extension_meta );
	}

	public function __construct() {
		add_action( 'admin_head', function() { echo $this->css; } );
		add_action( 'wp_dashboard_setup', array( $this, 'register_widget' ) );
	}

	/**
	 * Register widget for Dashboard
	 *
	 * @return void
	 */
	public function register_widget() {
		wp_add_dashboard_widget( 'yotpo_dashboard_widget', 'Yotpo Reviews', array( $this, 'get_metrics' ) );
	}

	public function get_metrics() {
		$settings_handler  = YRFW_Settings_File::get_instance();
		$this->settings    = $settings_handler->get_settings();
		$this->api = YRFW_API_Wrapper::get_instance();
		$this->api->init( $this->settings['app_key'], $this->settings['secret'] );
		$curl = $this->api->get_curl();
		$body = [ 'utoken' => $this->api->get_token() ];
		$curl->appendRequestHeader( 'Content-Type', 'application/json' );
		$response = $curl->get( $this->api->get_base_uri() . "/apps/{$this->settings['app_key']}/account_usages/metrics", $body );
		if ( 200 !== $response->statusCode ) {
			return;
		}
		$response = json_decode( $response );
		$this->return_html( $response->response );
	}

	public function number_format_short( $n, $precision = 1 ) {
		if ( $n < 900 ) {
			// 0 - 900
			$n_format = number_format( $n, $precision );
			$suffix   = '';
		} elseif ( $n < 900000 ) {
			// 0.9k-850k
			$n_format = number_format( $n / 1000, $precision );
			$suffix   = 'K';
		} elseif ( $n < 900000000 ) {
			// 0.9m-850m
			$n_format = number_format( $n / 1000000, $precision );
			$suffix   = 'M';
		} elseif ( $n < 900000000000 ) {
			// 0.9b-850b
			$n_format = number_format( $n / 1000000000, $precision );
			$suffix   = 'B';
		} else {
			// 0.9t+
			$n_format = number_format( $n / 1000000000000, $precision );
			$suffix   = 'T';
		}
		if ( $precision > 0 ) {
			$dotzero  = '.' . str_repeat( '0', $precision );
			$n_format = str_replace( $dotzero, '', $n_format );
		}
		return $n_format . $suffix;
	}

	private function return_html( object $metrics ) {
		?>
		<ul class="yotpo-metrics">
			<?php foreach ( $metrics as $key => $value ) : ?>
			<?php $key = ucwords( str_replace( '_', ' ', $key ) ); ?>
			<li><div class="metrics-value"><?php echo $this->number_format_short( $value ); ?></div><div class="metrics-key"><?php echo $key; ?></div></li>
			<?php endforeach; ?>
		</ul>
		<a class="button" href="https://yap.yotpo.com/#/preferredAppKey=<?php echo $this->settings['app_key']; ?>">Go to Yotpo</a>
		<?php
	}
}