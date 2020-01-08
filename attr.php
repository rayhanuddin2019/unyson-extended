<?php if ( ! defined( 'ABSPATH' ) ) die( 'Forbidden' );
/**
 * Plugin Name: Attr option
 * Plugin URI: http://wpdew.io/
 * Description: A customizer.
 * Version: 2.7.22
 * Author: themewinter
 * Author URI: http://themewinter.com
 * License: GPL2+
 * Text Domain: attr
 * Domain Path: /framework/languages
 */

if (defined('ATTR')) {

} else {
	require dirname( __FILE__ ) . '/framework/bootstrap.php';

	{
		/** @internal */
		function _action_attr_plugin_activate() {
			update_option( '_attr_plugin_activated', true, false ); // add special option (is used in another action)
		  
			if ( did_action( 'after_setup_theme' ) && ! did_action( 'attr_init' ) ) {
				_action_init_framework(); // load (prematurely) the plugin
				do_action( 'attr_plugin_activate' );
			}
			$uploads = wp_upload_dir();

	        
            $path =  $uploads['basedir'] . '/attr-backup';
			$windows_network_path = isset( $_SERVER['windir'] ) && in_array( substr( $path, 0, 2 ),
			array( '//', '\\\\' ),
			true );
			$fixed_path   = untrailingslashit( str_replace( array( '//', '\\' ), array( '/', '/' ), $path ) );

			if ( empty( $fixed_path ) && ! empty( $path ) ) {
				$fixed_path = '/';
			}

			if ( $windows_network_path ) {
				$fixed_path = '//' . ltrim( $fixed_path, '/' );
			}

			if(!file_exists($fixed_path)) wp_mkdir_p($fixed_path);	
		}

		register_activation_hook( __FILE__, '_action_attr_plugin_activate' );

		/** @internal */
		function _action_attr_plugin_check_if_was_activated() {
			if (get_option('_attr_plugin_activated')) {
				delete_option('_attr_plugin_activated');

				do_action('attr_after_plugin_activate');
			}
		}
		add_action(
			'current_screen', // as late as possible, but to be able to make redirects (content not started)
			'_action_attr_plugin_check_if_was_activated',
			100
		);

	
		function _action_attr_delete_blog( $blog_id, $drop ) {
			if ($drop) {
				global $wpdb; /** @var WPDB $wpdb */

				// delete old termmeta table
				$wpdb->query("DROP TABLE IF EXISTS `{$wpdb->prefix}attr_termmeta`;");
			}
		}
		add_action( 'delete_blog', '_action_attr_delete_blog', 10, 2 );

		/** @internal */
		function _filter_attr_plugin_action_list( $actions ) {
			return apply_filters( 'attr_plugin_action_list', $actions );
		}
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), '_filter_attr_plugin_action_list' );

		/** @internal */
		function _action_attr_textdomain() {
			load_plugin_textdomain( 'attr', false, plugin_basename( dirname( __FILE__ ) ) . '/framework/languages' );
		}
		add_action( 'attr_before_init', '_action_attr_textdomain', 3 );

		/** @internal */
		function _filter_attr_tmp_dir( $dir ) {
			
			return dirname( __FILE__ ) . '/tmp';
		}
		add_filter( 'attr_tmp_dir', '_filter_attr_tmp_dir' );

		/** @internal */
		final class _ATTR_Update_Hooks {
			public static function _init() {
				add_filter( 'upgrader_pre_install',  array(__CLASS__, '_filter_attr_check_if_plugin_pre_update'),  9999, 2 );
				add_filter( 'upgrader_post_install', array(__CLASS__, '_filter_attr_check_if_plugin_post_update'), 9999, 2 );
				add_action( 'automatic_updates_complete', array(__CLASS__, '_action_attr_automatic_updates_complete') );
			}

			public static function _filter_attr_check_if_plugin_pre_update( $result, $data ) {
				if (
					!is_wp_error($result)
					&&
					isset( $data['plugin'] )
					&&
					plugin_basename( __FILE__ ) === $data['plugin']
				) {
					/**
					 * Before plugin update
					 * The plugin was already download and extracted to a temp directory
					 * and it's right before being replaced with the new downloaded version
					 */
					do_action( 'attr_plugin_pre_update' );
				}

				return $result;
			}

			public static function _filter_attr_check_if_plugin_post_update( $result, $data ) {
				if (
					!is_wp_error($result)
					&&
					isset( $data['plugin'] )
					&&
					plugin_basename( __FILE__ ) === $data['plugin']
				) {
					/**
					 * After plugin successfully updated
					 */
					do_action( 'attr_plugin_post_update' );
				}

				return $result;
			}

			public static function _action_attr_automatic_updates_complete($results) {
				if (!isset($results['plugin'])) {
					return;
				}

				foreach ($results['plugin'] as $plugin) {
					if (plugin_basename( __FILE__ ) === strtolower($plugin->item->plugin)) {
						do_action( 'attr_automatic_update_complete', $plugin->result );
						break;
					}
				}
			}
		}
		_ATTR_Update_Hooks::_init();
	}
}
