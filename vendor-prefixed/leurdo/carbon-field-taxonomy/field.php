<?php

namespace Whisk\Recipes\Vendor\Carbon_Field_Taxonomy;

use Whisk\Recipes\Vendor\Carbon_Fields\Carbon_Fields;

define( 'Whisk\\Recipes\\Vendor\\Carbon_Field_Taxonomy\\DIR', __DIR__ );
define( 'CARBON_TAXONOMY_VERSION', '1.0.1');

if ( ! is_cli() ) {
	Carbon_Fields::extend(
		Taxonomy_Field::class,
		function ( $container ) {
			return new Taxonomy_Field(
				$container['arguments']['type'],
				$container['arguments']['name'],
				$container['arguments']['label']
			);
		}
	);
}

/**
 * Check if process is running in cli
 *
 * @return bool
 */

function is_cli() {
	if ( defined( 'STDIN' ) ) {
		return true;
	}
	if ( php_sapi_name() === 'cli' ) {
		return true;
	}
	if ( ! array_key_exists( 'REQUEST_METHOD', $_SERVER ) ) {
		return true;
	}

	return false;
}
