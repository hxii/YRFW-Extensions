<?php
/**
 * Site-level Rich Snippets (Structured Data)
 * Usage: use [yrfw_site_rich_snippet] shortcode with an optional 'hide' argument (without quotes) to hide the text.
 */
class YRFW_Site_Rich_Snippets {

	protected $extension_meta = [
		'extension_name'        => 'Site Level Rich Snippets',
		'extension_description' => '',
		'extension_version'     => '0.1',
		'extension_author'      => 'Paul Glushak',
		'extension_url'         => 'https://github.com/hxii/YRFW-Extensions',
	];

	public function register_extension() {
		$extension_handler = YRFW_Extensions::get_instance();
		$extension_handler->register_extension( $this->extension_meta );
	}

	public function __construct() {
		add_shortcode( 'yrfw_site_rich_snippet', array( $this, 'display_snippet' ) );
	}

	public function display_snippet( $args ) {
		global $settings_instance;
		$curl = YRFW_API_Wrapper::get_instance();
		$curl->init( $settings_instance['app_key'], $settings_instance['secret'] );
		$bottomline = json_decode( $curl->get_site_bottomline() );
		$data       = [
			'@context'        => 'http://schema.org',
			'@type'           => 'Organization',
			'name'            => get_bloginfo( 'name' ),
			'url'             => get_bloginfo( 'url' ),
			'logo'            => '',
			'aggregateRating' => [
				'@type'       => 'AggregateRating',
				'ratingValue' => $bottomline->response->bottomline->average_score ?: '0',
				'bestRating'  => '5',
				'reviewCount' => $bottomline->response->bottomline->total_reviews ?: '0',
			],
		];
		$hidden = ( isset( $args[0] ) && 'hide' === $args[0] ) ? ' style="display:none;"' : '';
		echo '<script type="application/ld+json">' . json_encode( $data, JSON_PRETTY_PRINT ) . '</script>';
		echo "<span{$hidden}>Rated {$bottomline->response->bottomline->average_score} out of 5 based on {$bottomline->response->bottomline->total_reviews} reviews.</span>";
	}
}