<?php
/**
 * Client Access Settings class
 *
 * @author Will Gorham
 * @package WMG\ClientAccess
 * @since 1.2
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Users settings class.
 *
 * @class CA_Settings
 */
class CA_Settings {

  /**
   * Initialize settings.
   *
   */
  public static function init() {
    add_action( 'admin_init', array( __CLASS__, 'initialize_settings' ) );
    add_action( 'admin_menu', array( __CLASS__, 'add_settings_page' ) );
  }

  /**
   * Register the settings group to be stored in the DB.
   *
   */
  public static function initialize_settings() {
    register_setting(
      'client-access', // Option group ID.
      'wmg-client-access' // Option name in the database.
    );
  }

  /**
   * Register the settings page and admin menu item.
   *
   */
  public static function add_settings_page() {
    $page_title = 'Client Access Settings';
    $menu_item_title = 'Client Access';
    $capability = 'manage_options';
    $page_slug = 'client-access';
    $content_callback = array( __CLASS__, 'render_settings_page' );

    add_options_page(
      $page_title,
      $menu_item_title,
      $capability,
      $page_slug,
      $content_callback
    );
  }

  /**
   * Render the settings page content.
   *
   */
  public static function render_settings_page() {
    ?>
    <div class="wrap">
      <h1>Client Access Settings</h1>
      <form action='options.php' method='post'>

        <?php
        settings_fields( 'client-access' );
        do_settings_sections( 'client-access' );
        submit_button();
        ?>

      </form>
    </div>
    <?php
  }

} // CA_Settings
