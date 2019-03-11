<?php
/**
 * Client Access main plugin class
 *
 * @author Will Gorham
 * @package WMG\ClientAccess
 * @since 1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Main plugin class.
 *
 * @class Client_Access
 */
final class Client_Access {

  /* Plugin version. */
  const VERSION = '3.1.2';

  /**
   * Single instance of this class.
   *
   * @var Client_Access
   */
  private static $_instance = null;

  /**
   * Client_Access class constructor.
   *
   */
  public function __construct() {
    // Wait until plugins_loaded so pluggable functions are available (e.g. wp_get_current_user).
    add_action( 'plugins_loaded', array( $this, 'load' ) );
  }

  /**
   * Get the main Client_Access instance.
   *
   * Ensures only one instance of the class is loaded.
   *
   * @static
   * @return Client_Access - Main instance
   */
  public static function instance() {
    if ( is_null( self::$_instance ) ) {
      self::$_instance = new self();
    }
    return self::$_instance;
  }

  /**
   * Cloning is forbidden.
   *
   */
  public function __clone() {
    _doing_it_wrong( __FUNCTION__, __( 'Nah.', 'client-access' ), '1.0.0' );
  }

  /**
   * Unserializing instances of this class is forbidden.
   *
   */
  public function __wakeup() {
    wc_doing_it_wrong( __FUNCTION__, __( 'Nah.', 'client-access' ), '1.0.0' );
  }

  /**
   * The plugin URL.
   *
   */
  public function plugin_url() {
    return plugin_dir_url( CLIENT_ACCESS_PLUGIN_FILE );
  }

  /**
   * The plugin path.
   *
   */
  public function plugin_path() {
    return plugin_dir_path( CLIENT_ACCESS_PLUGIN_FILE );
  }

  /**
   * Bootstrap the plugin.
   *
   */
  public function load() {
    $this->includes();
    $this->init();

    add_action( 'admin_init', array( $this, 'update'), 10 );
  }

  /**
   * Include required core files used in admin and on the frontend.
   *
   */
  private function includes() {

    // Abstracts
    require_once $this->plugin_path() . 'includes/abstracts/abstract-ca-role.php';

    // Classes
    require_once $this->plugin_path() . 'includes/class-ca-client-role.php';
    require_once $this->plugin_path() . 'includes/class-ca-site-manager-role.php';
    require_once $this->plugin_path() . 'includes/class-ca-document-fields.php';
    require_once $this->plugin_path() . 'includes/class-ca-settings.php';
    require_once $this->plugin_path() . 'includes/class-ca-user-management.php';
  }

  /**
   * Run update routines.
   *
   */
  public function update() {
    $current_version = get_option( 'wmg-client-access-version', 0 );

    if ( version_compare( self::VERSION, $current_version, '>' ) ) {
      // Migrate from old Client Capabilities plugin
      $settings = get_option( 'wmg-client-capabilities', 0 );
      if ( $settings ) {
        update_option( 'wmg-client-access', $settings );
        delete_option( 'wmg-client-capabilities' );
      }

      // Update user roles
      CA_Client_Role::update();
      CA_Site_Manager_Role::update();

      // Save new version
      update_option( 'wmg-client-access-version', self::VERSION );
    }
  }

  /**
   * Initialize plugin.
   *
   */
  private function init() {
    CA_Site_Manager_Role::init();
    CA_Client_Role::init();
    CA_Document_Fields::init();
    CA_Settings::init();
    CA_User_Management::init();
  }

} // Client_Access
