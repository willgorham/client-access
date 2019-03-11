<?php
/**
 * Client Access Site Manager Role class
 *
 * @author Will Gorham
 * @package WMG\ClientAccess
 * @since 3.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Site Manager user role class.
 *
 * @class CA_Site_Manager_Role
 */
class CA_Site_Manager_Role extends CA_Role {

  /**
   * User role slug.
   *
   * @var string
   */
  protected static $role_slug = 'ca_site_manager';

  /**
   * User role display name.
   *
   * @var string
   */
  protected static $role_name = 'Site Manager';

  /**
   * User role capabilities.
   *
   * @var array
   */
  protected static $role_capabilities = array(
    'read'                     => true,
    'edit_pages'               => true,
    'edit_others_pages'        => true,
    'edit_published_pages'     => true,
    'publish_pages'            => true,
    'delete_pages'             => true, // Unique to role
    'delete_others_pages'      => true, // Unique to role
    'delete_published_pages'   => true, // Unique to role
    'upload_files'             => true,
    'tablepress_list_tables'   => true,
    'tablepress_add_tables'    => true,
    'tablepress_edit_tables'   => true,
    'tablepress_delete_tables' => true,
    'tablepress_copy_tables'   => true,
    'tablepress_import_tables' => true,
    'jot_user_capability'      => true,
  );

  /**
   * Add additional actions on 'init' hook
   *
   */
  protected static function role_init_hooks() {
    add_filter( 'gettext', array( __CLASS__, 'translate_trash_text' ), 10, 3 );
  }

  /**
   * Change Move to Trash text.
   *
   * @param  string $translation Translated text
   * @param  string $text        Original text
   * @param  string $domain      Text domain
   * @return string              Translated text
   */
  public static function translate_trash_text( $translation, $text, $domain ) {
    if ( 'Move to Trash' === $translation ) {
      $translation = 'Delete';
    }

    return $translation;
  }

  /**
   * Enqueue admin styles.
   *
   */
  public static function enqueue_admin_styles() {
    $ca = client_access();

    wp_enqueue_style(
      'ca-site-manager-role-admin-styles',
      $ca->plugin_url() . 'css/admin/ca-site-manager-role.css',
      array(),
      $ca::VERSION
    );

  }

} // CA_Site_Manager_Role
