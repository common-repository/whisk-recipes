<?php
/**
 * Plugin Name: Whisk Recipes
 * Plugin URI: https://wordpress.org/plugins/whisk-recipes/
 * Description: Whisk Recipes for WordPress is a free fully-featured plugin, created for creators and food-lovers. It allows you to easily add recipes to your website and have them instantly integrated into Whisk ecosystem. Your friends won’t have to print out recipes from your website and worry about buying the right amount of ingredients.
 * Author: whisk.com
 * Author URI: https://whisk.com/
 * Requires at least: 5.0
 * Tested up to: 5.7.1
 * Version: 1.2.0
 * Stable tag: 1.2.0
 *
 * Text Domain: whisk-recipes
 * Domain Path: /languages/
 *
 * @package whisk-recipes
 * @author  whisk.com
 */

namespace Whisk\Recipes;

use Whisk\Recipes\Vendor\Auryn\Injector;

// @codeCoverageIgnoreStart
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
// @codeCoverageIgnoreEnd

/**
 * Plugin version.
 */
define( 'WHISK_RECIPES_VERSION', '1.2.0' );

/**
 * Path to the plugin dir.
 */
define( 'WHISK_RECIPES_PATH', __DIR__ );

/**
 * Plugin dir url.
 */
define( 'WHISK_RECIPES_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );

/**
 * Main plugin file.
 */
define( 'WHISK_RECIPES_FILE', __FILE__ );

/**
 * Plugin prefix.
 */
define( 'WHISK_RECIPES_PREFIX', 'whisk-recipes' );

/**
 * Plugin slug.
 */
define( 'WHISK_RECIPES_SLUG', 'whisk_recipes' );

/**
 * Init plugin on plugin load.
 */
require_once constant( 'WHISK_RECIPES_PATH' ) . '/vendor/woocommerce/action-scheduler/action-scheduler.php';
require_once constant( 'WHISK_RECIPES_PATH' ) . '/vendor/autoload.php';

$whisk_recipes = new Main( new Injector() );
$whisk_recipes->init();
