<?php

require_once YRFW_PLUGIN_PATH . 'inc/Helpers/class-yrfw-csv-helper.php';

/**
 * Review export
 */
class YRFW_Export_Products extends YRFW_CSV_Helper {

	protected $products_handler;
	protected $csv_helper;

	public function __construct() {
		add_action( 'yrfw_extensions_admin_header', array( $this, 'form' ) );
		add_action( 'yrfw_extensions_settings', array( $this, 'html' ) );
	}

	protected $extension_meta = [
		'extension_name'        => 'Product Catalog Export',
		'extension_description' => 'This extension allows you to export your product catalog in a Yotpo-ready format',
		'extension_version'     => '0.1',
		'extension_author'      => 'Paul Glushak',
		'extension_url'         => 'https://github.com/hxii/YRFW-Extensions',
	];

	public function register_extension() {
		$extension_handler = YRFW_Extensions::get_instance();
		$extension_handler->register_extension( $this->extension_meta );
	}

	public function init() {
		// do nothing.
	}
	
	public function export() {
		parent::__construct( $this->process_products(), $this->header_rows );
	}

	protected $header_rows = [
		'Product ID',
		'Product Name',
		'Product Description',
		'Product URL',
		'Product Image URL',
		'Product Price',
		'Currency',
		'Spec UPC',
		'Spec SKU',
		'Spec Brand',
		'Spec MPN',
		'Spec ISBN',
	];

	private function process_products() {
		$this->products_handler = YRFW_Product_Cache::get_instance();
		$products               = $this->products_handler->get_all_products_data();
		$data                   = array();
		foreach ( $products as $product ) {
			$current_product = array();
			$current_product = [
				'Product ID'          => $product['id'],
				'Product Name'        => $product['name'],
				'Product Description' => $product['description'],
				'Product URL'         => $product['url'],
				'Product Image URL'   => $product['image'],
				'Product Price'       => $product['price'],
				'Currency'            => YRFW_CURRENCY,
				'Spec UPC'            => $product['specs']['upc'] ?? '',
				'Spec SKU'            => $product['specs']['external_sku'] ?? '',
				'Spec Brand'          => $product['specs']['brand'] ?? '',
				'Spec MPN'            => $product['specs']['mpn'] ?? '',
				'Spec ISBN'           => $product['specs']['isbn'] ?? '',
			];
			$data[]          = $current_product;
		}
		return $data;
	}

	public function html() {
		?>
		<hr>
		<form action="" method="post" accept-charset="utf-8">
			<?php wp_nonce_field( 'product_export', 'yotpo_product_export_form' ); ?>
			<label for="export_products"><?php esc_html_e( 'Export Products', 'yrfw' ); ?></label><span class="dashicons dashicons-editor-help" data-toggle="tooltip" title="Export native WooCommerce reviews in an import-ready format for Yotpo."></span>
			<div class="form-group">
				<button type="submit" name="export_products" id="export_products" class="btn btn-info" value="true"><?php esc_html_e( 'Export Products', 'yrfw' ); ?></button>
				<small class="form-text text-muted"><?php esc_html_e( 'This will export all existing native WooCommerce reviews in an import-ready format for Yotpo.', 'yrfw' ); ?>.</small>
			</div>
		</form>
		<?php
	}

	public function form() {
		if ( isset( $_POST['export_products'] ) && wp_verify_nonce( $_POST['yotpo_product_export_form'], 'product_export' ) ) {
			$this->export();
			$export_file = parent::generate_csv();
			if ( $export_file ) {
				new YRFW_Messages( esc_html__( 'Product catalog successfully exported to', 'yrfw' ) . ' <a class="alert-link" href="' . YRFW_PLUGIN_URL . '/' . $export_file . '">' . $export_file . '</a>', 'success' );
			}
		}
	}

}
