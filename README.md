# YRFW-Extensions
 Extensions for the Yotpo Reviews for WooCommerce plugin

# Available Extensions
- YRFW Dashboard - Shows Yotpo metrics in a glance.
- YRFW Debug Page - Allows (securely) accessing basic debug information to yotpo support.
- YRFW Export Products - Export your product catalog in a Yotpo-import-ready format.
- YRFW Rich Snippets - Outputs rich snippet markup on product pages.

# Writing Extensions
Extensions are fairly simple at this point and require a couple of things:
1. Add the required methods and properties (below) to the class.
2. Put the file in the extensions folder.

## Extension Meta
```PHP
protected $extension_meta = [
    'extension_name'        => 'Dashboard',
    'extension_description' => 'Your Yotpo stats in a glance',
    'extension_version'     => '0.1',
    'extension_author'      => 'Paul Glushak',
    'extension_url'         => 'https://github.com/hxii/YRFW-Extensions',
];
```

## Registering the Extension
```PHP
public function register_extension() {
    $extension_handler = YRFW_Extensions::get_instance();
    $extension_handler->register_extension( $this->extension_meta );
}
```