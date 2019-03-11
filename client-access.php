<?php
/**
 * Plugin Name: Client Access
 * Plugin URI: https://github.com/willgorham/client-access/
 * Description: Creates custom user roles with restricted capabilities, and a Documents field type with upload and contextual note capabilities attached to Pages.
 * Version: 3.1.2
 * Author: Will Gorham
 * Author URI: https://willgorham.com
 *
 * Text Domain: client-access
 *
 * @package WMG\ClientAccess
 * @author Will Gorham
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Define CLIENT_ACCESS_PLUGIN_FILE.
if ( ! defined( 'CLIENT_ACCESS_PLUGIN_FILE' ) ) {
  define( 'CLIENT_ACCESS_PLUGIN_FILE', __FILE__ );
}

// Include the main plugin class.
if ( ! class_exists( 'Client_Access' ) ) {
  require_once( dirname( __FILE__ ) . '/includes/class-client-access.php' );
}

/**
 * Retrieves the main plugin class instance.
 *
 * @return Client_Access - Instance of the main plugin class.
 */
function client_access() {
  return Client_Access::instance();
}

// Initialize the class instance.
client_access();
