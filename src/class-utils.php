<?php
/**
 * Utils
 *
 * @package whisk-recipes
 */

namespace Whisk\Recipes;

use Whisk\Recipes\Vendor\Carbon_Fields\Helper\Helper;
use wpdb;

/**
 * Class Utils
 *
 * @package whisk-recipes
 */
class Utils {
	/**
	 * Utils constructor.
	 */
	public static function is_debug() {
		return ( defined( 'WP_DEBUG' ) && WP_DEBUG && ! empty( $_SERVER['SERVER_ADDR'] ) && '127.0.0.1' === $_SERVER['SERVER_ADDR'] );
	}

	/**
	 * Retrieves the URL of a file in the plugin.
	 *
	 * @param string $file Path.
	 *
	 * @return string
	 */
	public static function get_plugin_file_uri( $file = '' ) {
		$file = ltrim( $file, '/' );

		if ( empty( $file ) ) {
			$url = WHISK_RECIPES_URL;
		} else {
			$url = WHISK_RECIPES_URL . '/' . $file;
		}

		return apply_filters( 'whisk_recipes_plugin_file_uri', $url, $file );
	}

	/**
	 * Get full plugin path.
	 *
	 * @return string
	 */
	public static function get_plugin_path() {
		return WHISK_RECIPES_PATH;
	}

	/**
	 * Get plugin vesrion.
	 *
	 * @return string
	 */
	public static function get_plugin_version() {
		return WHISK_RECIPES_VERSION;
	}

	/**
	 * Get plugin file.
	 *
	 * @return string
	 */
	public static function get_plugin_file() {
		return WHISK_RECIPES_FILE;
	}

	/**
	 * Get plugin slug.
	 *
	 * @return string
	 */
	public static function get_plugin_slug() {
		return WHISK_RECIPES_SLUG;
	}

	/**
	 * Get plugin prefix.
	 *
	 * @return string
	 */
	public static function get_plugin_prefix() {
		return WHISK_RECIPES_PREFIX;
	}

	/**
	 * Get user IP address.
	 *
	 * @return string
	 */
	public static function get_user_ip() {
		// Get real visitor IP behind CloudFlare network.
		if ( isset( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) {
			$_SERVER['REMOTE_ADDR']    = $_SERVER['HTTP_CF_CONNECTING_IP'];
			$_SERVER['HTTP_CLIENT_IP'] = $_SERVER['HTTP_CF_CONNECTING_IP'];
		}

		$client  = @$_SERVER['HTTP_CLIENT_IP'];
		$forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
		$remote  = $_SERVER['REMOTE_ADDR'];

		if ( filter_var( $client, FILTER_VALIDATE_IP ) ) {
			$ip = $client;
		} elseif ( filter_var( $forward, FILTER_VALIDATE_IP ) ) {
			$ip = $forward;
		} else {
			$ip = $remote;
		}

		return $ip;
	}

	/**
	 * Get user agent.
	 *
	 * @return null|string
	 */
	public static function get_user_agent() {
		return isset( $_SERVER['HTTP_USER_AGENT'] )
			? trim( $_SERVER['HTTP_USER_AGENT'] )
			: null;
	}

	/**
	 * Получить шаблон, передав в него переменные
	 *
	 * @param string      $slug      слюг шаблона
	 * @param string|null $name      имя шаблона
	 * @param array       $params    массив передаваемых переменных
	 * @param string      $query_var имя переменной для set_query_var
	 * @param array       $defaults  имя переменной для set_query_var
	 *
	 * @return string
	 */
	public static function get_template_parts( $slug, $name = null, $params = array (), $query_var = '', $defaults = array () ) {

		if ( ! empty( $defaults ) ) {
			$params = wp_parse_args( $params, $defaults );
		}
		// Хукаем
		do_action( 'get_template_part_' . $slug, $slug, $name );

		// Буферизуем
		ob_start();

		// Формируем имя шаблона.
		$templates = array ();
		$name      = ( string ) $name;

		// Если кроме слюга передано имя,
		// добавим его к названию шаблона.
		if ( '' !== $name ) {
			$templates[] = "{$slug}-{$name}.php";
		}

		$templates[] = "{$slug}.php";

		// Извлекаем переменные, если они переданы.
		if ( ! empty( $params ) ) {
			if ( $query_var ) {
				set_query_var( 'template_' . $query_var . '_vars', $params );
			} else {
				extract( $params, EXTR_SKIP );
			}
		}

		// Подключить шаблоны
		foreach ( $templates as $template ) {
			$template = self::get_plugin_path() . '/' . $template;

			if ( file_exists( $template ) ) {
				include $template;
			}
		}

		// Вернем отрендеренный шаблон.
		return ob_get_clean();
	}

	/**
	 * Check if Classic Editor plugin is active.
	 *
	 * @link https://kagg.eu/how-to-catch-gutenberg/
	 *
	 * @return bool
	 */
	public static function is_classic_editor_plugin_active() {
		// @codeCoverageIgnoreStart
		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		// @codeCoverageIgnoreEnd

		return is_plugin_active( 'classic-editor/classic-editor.php' );
	}

	/**
	 * Check if Block Editor is active.
	 * Must only be used after plugins_loaded action is fired.
	 *
	 * @link https://kagg.eu/how-to-catch-gutenberg/
	 *
	 * @return bool
	 */
	public static function is_gutenberg_editor_active() {

		// Gutenberg plugin is installed and activated.
		$gutenberg = ! ( false === has_filter( 'replace_editor', 'gutenberg_init' ) );

		// Block editor since 5.0.
		$block_editor = version_compare( $GLOBALS['wp_version'], '5.0-beta', '>' );

		if ( ! $gutenberg && ! $block_editor ) {
			return false;
		}

		if ( self::is_classic_editor_plugin_active() ) {
			$editor_option       = get_option( 'classic-editor-replace' );
			$block_editor_active = [ 'no-replace', 'block' ];

			return in_array( $editor_option, $block_editor_active, true );
		}

		return true;
	}

	/**
	 * Schedule flush rewrite rules.
	 */
	public static function schedule_flush_rewrite_rules() {
		update_option( self::get_plugin_slug() . '_flush_rewrite_rules', 1, true );
	}

	/**
	 * Get all public post types.
	 *
	 * @return array
	 */
	public static function get_all_public_post_types() {
		$data = [];
		$args = [ 'public' => true ];

		$post_types = get_post_types( $args, 'objects' );

		foreach ( $post_types as $post_type ) {
			$data[ $post_type->name ] = sprintf( '%s (%s)', $post_type->label, $post_type->name );
		}

		return $data;
	}

	/**
	 * Get all public taxonomies.
	 *
	 * @return array
	 */
	public static function get_all_public_taxonomies() {
		$data = [];
		$args = [ 'public' => true ];

		$taxonomies = get_taxonomies( $args, 'objects' );

		foreach ( $taxonomies as $taxonomy ) {
			$data[ $taxonomy->name ] = sprintf( '%s (%s)', $taxonomy->label, $taxonomy->name );
		}

		return $data;
	}

	/**
	 * Get all public meta fields.
	 *
	 * @return array
	 * @license https://wordpress.stackexchange.com/questions/58834/echo-all-meta-keys-of-a-custom-post-type
	 */
	public static function get_all_public_meta() {
		global $wpdb;

		$cache_key = 'whisk_all_public_meta';

		// Get data from transient cache.
		$cached = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$data = [];

		$black_list = [
			'_edit_l',
			'_oembed',
			'_custom',
			'_wp_old',
			'_wp_att',
			'_wp_pag',
		];

		$not_in = self::prepare_in( $black_list );

		$sql = "SELECT meta_key
				FROM {$wpdb->postmeta}
				WHERE SUBSTRING(meta_key, 1, 7) NOT IN ({$not_in})
				ORDER BY meta_key
				LIMIT 5000";

		// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$meta = $wpdb->get_col( $sql );
		// phpcs:enable WordPress.DB.PreparedSQL.NotPrepared

		$meta = array_unique( $meta );

		if ( $meta ) {
			foreach ( $meta as $item ) {
				$data[ $item ] = $item;
			}
		}

		// Save data to transient cache.
		set_transient( $cache_key, $data, 1 * HOUR_IN_SECONDS );

		return $data;
	}

	/**
	 * Convert array of attributes to html attributes.
	 *
	 * @param array $attr Array of attributes.
	 *
	 * @return string
	 */
	public static function array_to_html_attributes( $attr ) {
		$html = '';
		$attr = array_map( 'esc_attr', $attr );

		foreach ( $attr as $name => $value ) {
			$html .= " $name=" . '"' . $value . '"';
		}

		return $html;
	}

	/**
	 * Changes array of items into string of items, separated by comma and sql-escaped
	 *
	 * @see https://coderwall.com/p/zepnaw
	 * @global wpdb       $wpdb
	 *
	 * @param mixed|array $items  item(s) to be joined into string.
	 * @param string      $format %s or %d.
	 *
	 * @return string Items separated by comma and sql-escaped
	 */
	public static function prepare_in( $items, $format = '%s' ) {
		global $wpdb;

		$items    = (array) $items;
		$how_many = count( $items );
		if ( $how_many > 0 ) {
			$placeholders    = array_fill( 0, $how_many, $format );
			$prepared_format = implode( ',', $placeholders );
			$prepared_in     = $wpdb->prepare( $prepared_format, $items ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		} else {
			$prepared_in = '';
		}

		return $prepared_in;
	}

	/**
	 * Download media from URL.
	 *
	 * @param string $url         Media URL.
	 * @param string $description Media description.
	 *
	 * @return int|\WP_Error
	 */
	public static function media_sideload( $url, $description = '' ) {

		$tmp = download_url( $url );

		if ( is_wp_error( $tmp ) ) {
			@unlink( $tmp ); // phpcs:ignore
		}

		$file_array = array (
			'name'     => basename( $url ),
			'tmp_name' => $tmp,
			'error'    => 0,
			'size'     => filesize( $tmp ),
		);

		$post_data = array (
			'guid'         => $url,
			'post_title'   => $description,
			'post_content' => $description,
		);

		$media_id = media_handle_sideload( $file_array, 0, $description, $post_data );

		if ( is_wp_error( $media_id ) ) {
			@unlink( $tmp ); // phpcs:ignore
		}

		return $media_id;
	}

	/**
	 * Get media ID by given field and value.
	 *
	 * @param string $field Field name.
	 * @param string $value Value of field.
	 *
	 * @return int
	 */
	public static function get_media_by( $field, $value ) {
		global $wpdb;

		if ( 'guid' === $field ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			return (int) $wpdb->get_var(
				$wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE guid='%s';", $value )
			);
		}

		return 0;
	}

	/**
	 * Recursive version of wp_parse_args.
	 *
	 * @param mixed $a A.
	 * @param mixed $b B.
	 *
	 * @return array/mixed
	 */
	public static function wp_parse_args( &$a, $b ) {
		$a      = (array) $a;
		$b      = (array) $b;
		$result = $b;
		foreach ( $a as $k => &$v ) {
			if ( is_array( $v ) && isset( $result[ $k ] ) ) {
				$result[ $k ] = self::wp_parse_args( $v, $result[ $k ] );
			} else {
				$result[ $k ] = $v;
			}
		}

		return $result;
	}

	/**
	 * Convert minutes to array of hours and minutes.
	 *
	 * @param int $time Time in minutes.
	 *
	 * @return array
	 */
	public static function convert_minutes_to_hours_and_minutes( $time ) {
		if ( $time < 1 ) {
			return array ( 0, 0 );
		}

		$hours   = floor( $time / 60 );
		$minutes = ( $time % 60 );

		return array ( $hours, $minutes );
	}

	/**
	 * Convert decimal to html entity.
	 *
	 * @param float $decimal Floating point numbers.
	 *
	 * @return string
	 */
	public static function convert_decimal_to_html_entity( $decimal ) {
		$decimal = (string) round( $decimal, 3 );

		$entities = array (
			'0.5'   => '½',
			'0.333' => '⅓',
			'0.25'  => '¼',
			'0.2'   => '⅕',
			'0.166' => '⅙',
			'0.142' => '⅐',
			'0.125' => '⅛',
			'0.111' => '⅑',
			'0.1'   => '⅒',

			'0.666' => '⅔',
			'0.4'   => '⅖',

			'0.75'  => '¾',
			'0.375' => '⅜',

			'0.8' => '⅘',

			'0.833' => '⅚',
			'0.625' => '⅝',

			'0.875' => '⅞',
		);

		if ( isset( $entities[ $decimal ] ) ) {
			return $entities[ $decimal ];
		}

		return $decimal;
	}

	/**
	 * Is mapping enabled?
	 *
	 * @return bool
	 */
	public static function is_mapping_enabled() {
		return Helper::get_theme_option( 'whisk_use_mapping' );
	}

	/**
	 * Restricted Grocers enabled?
	 *
	 * @return bool
	 */
	public static function is_restricted_grocers_enabled() {
		return (bool) get_option( '_whisk_enable_restricted_grocers', false );
	}
}
