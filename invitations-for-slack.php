<?php
/*
Plugin Name: Invitations for Slack
Plugin URI: http://rheinard.org
Description: Build a Slack community by allowing your visitors (or registered users) to invite themselves to your Slack team. Just add your Slack token and use the shortcodes wherever you want your visitors to be able to invite themselves from. Uses a convenient popup that communicates with Slack via the Slack API and WP REST API.
Version: 1.0.2
Author: Rheinard Korf
Author URI: http://rheinard.org
License: GPL2
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Text Domain: invitations-for-slack
*/

/**
 * @copyright 2013-2014 Rheinard Korf
 *
 * @license http://opensource.org/licenses/GPL-2.0 GNU General Public License, version 2 (GPL-2.0)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston,
 * MA 02110-1301 USA
 *
 */

/**
 * Bootstrap SlackInviter.
 *
 * This is the primary entry point for SlackInviter.
 *
 * @since 1.0.0
 */
SlackInviter::bootstrap();

/**
 * Class SlackInviter
 *
 * This forms the base for "Invitations for Slack". Its how we get/set settings
 * as well as other plugin information.
 *
 * The bootstrap() method is what "starts" the plugin
 */
class SlackInviter {

	public static $name;        // Name of plugin
	public static $version;     // Version of plugin
	public static $td;          // Text Domain
	public static $file;        // Plugin file without path
	public static $file_path;   // Plugin file including path
	public static $dir;         // Installed directory (path)
	public static $url;         // Plugin URL
	public static $where;       // plugins or mu-plugins
	public static $class_path;  // Path for classes called by autoloader

	/**
	 * Bootstrap the plugin
	 */
	public static function bootstrap() {

		// Get plugin details from Header
		$default_headers = array( 'name' => 'Plugin Name', 'version' => 'Version', 'td' => 'Text Domain' );
		$default_headers = get_file_data( __FILE__, $default_headers, 'plugin' );

		// Set plugin variables
		self::$name    = $default_headers['name'];
		self::$version = $default_headers['version'];
		self::$td      = $default_headers['td'];
		self::set_location_vars();
		self::$file      = str_replace( self::$dir, '', __FILE__ );
		self::$file_path = __FILE__;
		self::$class_path = 'invitations-for-slack'; // Used to replace "SlackInviter" in path with dir location

		// Initiate the autoloader
		require_once self::$dir . '/invitations-for-slack/autoloader.php';

		// Get our language depending on where plugin is installed
		// Name language file as "[text_domain]-[value in wp-config].mo"
		if ( self::$where == 'plugins' ) {
			load_plugin_textdomain( self::$td, false, self::$dir . '/invitations-for-slack/lang/' );
		} elseif ( self::$where == 'mu-plugins' ) {
			load_muplugin_textdomain( self::$td, '/invitations-for-slack/lang/' );
		}

		// Create activation/deactivation hooks
		register_activation_hook( self::$file_path, array( 'SlackInviter_Core', 'activated' ) );
		register_deactivation_hook( self::$file_path, array( 'SlackInviter_Core', 'deactivated' ) );

		// Init Core
		SlackInviter_Core::init();

		// Allow other inits
		do_action( 'invitations-for-slack/loaded' );

	}

	/**
	 * Where is the plugin installed?
	 */
	private static function set_location_vars() {
		if ( defined( 'WP_PLUGIN_URL' ) && defined( 'WP_PLUGIN_DIR' ) && file_exists( plugin_dir_path( __FILE__ ) . basename( __FILE__ ) ) ) {
			self::$where = 'plugins';
			self::$dir   = plugin_dir_path( __FILE__ );
			self::$url   = trailingslashit( plugins_url( '', __FILE__ ) );
		} else if ( defined( 'WPMU_PLUGIN_URL' ) && defined( 'WPMU_PLUGIN_DIR' ) && file_exists( WPMU_PLUGIN_DIR . '/' . basename( __FILE__ ) ) ) {
			self::$where = 'mu-plugins';
			self::$dir   = WPMU_PLUGIN_DIR;
			self::$url   = trailingslashit( WPMU_PLUGIN_URL );
		} else {
			wp_die( sprintf( __( 'Could not work out where %s is installed. Please reinstall.', self::$td ), self::$name ) );
		}
	}

	/**
	 * Get plugin settings
	 *
	 * @param $key
	 * @param null $default
	 *
	 * @return mixed
	 */
	public static function get_setting( $key, $default = null ) {
		return self::_setting( $key, null, $default );
	}

	/**
	 * Gets or Sets a plugin option
	 *
	 * @param $key
	 * @param null $value
	 * @param null $default
	 *
	 * @return mixed
	 */
	private static function _setting( $key, $value = null, $default = null ) {
		$option_string = 'invitations_for_slack';

		$options = get_option( $option_string, array() );

		if ( isset( $value ) ) {
			// Set value
			$options[ $key ] = $value;

			update_option( $option_string, $options );

			return $value;
		} else {
			// Get value
			if ( isset( $options[ $key ] ) ) {
				return $options[ $key ];
			} else {
				return $default;
			}
		}

	}

	/**
	 * Sets a plugin option.
	 *
	 * @param $key
	 * @param $value
	 *
	 * @return mixed
	 */
	public static function set_setting( $key, $value ) {
		return self::_setting( $key, $value, null );
	}

}
