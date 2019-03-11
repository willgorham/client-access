<?php
/**
 * Client Access User Management class
 *
 * @author Will Gorham
 * @package WMG\ClientAccess
 * @since 1.2
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * User management class.
 *
 * @class CA_User_Management
 */
class CA_User_Management {

  /**
   * Initialize user management features.
   *
   */
  public static function init() {
    // Hook user-related functions on init to ensure the current user is set up.
    add_action( 'init', array( __CLASS__, 'init_hooks' ), 10 );
    add_action( 'admin_init', array( __CLASS__, 'initialize_settings' ), 10 );

    // Subscriber restrictions.
    add_action( 'init', array( __CLASS__, 'subscriber_hide_admin_bar' ), 10 );
    add_filter( 'login_redirect', array( __CLASS__, 'subscriber_login_redirect' ), 10, 3 );
  }

  /**
   * Hook into actions and filters.
   *
   */
  public static function init_hooks() {
    if ( ( ! CA_Client_Role::user_has_this_role() &&
           ! CA_Site_Manager_Role::user_has_this_role() ) ||
         ! self::is_user_management_enabled() ) {
      return;
    }

    add_action( 'pre_get_users', array( __CLASS__, 'filter_user_query' ), 10, 1 );
    add_filter( 'editable_roles', array( __CLASS__, 'filter_roles_dropdown' ), 10, 1 );
    add_filter( 'user_has_cap', array( __CLASS__, 'add_user_management_caps' ), 10, 3 );
    add_filter( 'map_meta_cap', array( __CLASS__, 'map_user_management_caps' ), 10, 4 );
    add_filter( 'manage_users_columns', array( __CLASS__, 'remove_user_list_table_columns' ), 10, 1 );
    add_filter( 'user_row_actions', array( __CLASS__, 'remove_user_list_table_row_actions' ), 10, 1 );
    add_filter( 'views_users', array( __CLASS__, 'remove_user_list_table_view_filters' ), 10, 1 );
    add_filter( 'enable_edit_any_user_configuration', '__return_true', 20 );
  }

  /**
   * Register user-management settings section and fields.
   *
   */
  public static function initialize_settings() {
    add_settings_section(
      'client-access_user-management', // Section ID.
      '', // Section title.
      '', // Section header content callback.
      'client-access' // Option group ID.
    );

    add_settings_field(
      'user-management_enabled', // Field ID
      'Enable user management', // Field title.
      array( __CLASS__, 'render_settings_field_user_management_enabled' ), // Field render callback.
      'client-access', // Option group ID.
      'client-access_user-management' // Settings section ID.
    );

  }

  /**
   * Render user management Enabled settings field.
   *
   */
  public static function render_settings_field_user_management_enabled() {
    $options = get_option( 'wmg-client-access' );
    $option = isset( $options['user-management_enabled'] ) ? $options['user-management_enabled'] : '';
    ?>
    <label><input type='checkbox' name='wmg-client-access[user-management_enabled]' <?php checked( $option, 1 ); ?> value='1'>
      Allow all Site Managers on this site to create, edit, and delete Subscriber users.
    </label>
    <?php
  }

  /**
   * Check if user management is enabled.
   *
   * @return boolean If user management is enabled.
   */
  public static function is_user_management_enabled() {
    $options = get_option( 'wmg-client-access' );

    return isset( $options['user-management_enabled'] ) && $options['user-management_enabled'];
  }

  /**
   * Add capabilities for user management on the fly.
   *
   * @param array $user_caps      Current user capabilities.
   * @param array $requested_cap  Capability being checked.
   * @param array $args           Updated user capabilities.
   */
  public static function add_user_management_caps( $user_caps, $requested_cap, $args ) {
    $user_caps['list_users'] = true;
    $user_caps['create_users'] = true;
    $user_caps['edit_users'] = true;
    $user_caps['promote_users'] = true;
    $user_caps['remove_users'] = true;
    $user_caps['delete_users'] = true;

    return $user_caps;
  }

  /**
   * Map meta capabilities of users.
   *
   * @param  array $required_caps  Capabilities required to allow the requested capability.
   * @param  string $requested_cap Meta capability being evaluated.
   * @param  string $user_id       ID of the current user.
   * @param  array $args           Context about the capability check.
   * @return array                 Required capabilities for the requested meta capability.
   */
  public static function map_user_management_caps( $required_caps, $requested_cap, $user_id, $args ) {

    /**
     * Enable capabilities up front.
     */

    if ( $requested_cap === 'create_users' ) {
      // Force in case 'Add New Users' network option is disabled.
      $required_caps = array( 'create_users' );
    }

    if ( $requested_cap === 'edit_user' || $requested_cap === 'edit_users' ) {
      $required_caps = array( 'edit_users' );
    }

    /**
     * Then restrict capabilities based on context.
     * Only allow delete/remove on subscribers, and edit on subscribers and self.
     */

    if ( $requested_cap === 'delete_user' ||
         $requested_cap === 'remove_user' ||
         ( $requested_cap === 'edit_user' && $user_id !== $args[0] ) ) {
      $user = new WP_User( $args[0] );
      if ( ! in_array( 'subscriber', (array) $user->roles ) ) {
        $required_caps = array( 'do_not_allow' );
      }
    }

    return $required_caps;
  }

  /**
   * Adjust user query to only get subscriber and Client users.
   *
   * @param  WP_User_Query $query Query object.
   */
  public static function filter_user_query( $query ) {
    $allowed_roles = array(
      'subscriber',
      CA_Client_Role::get_role_slug(),
      CA_Site_Manager_Role::get_role_slug(),
    );
    $query_allowed_roles = $query->get( 'role__in' );

    if ( empty( $query_allowed_roles ) ) {
      $query->set( 'role__in', $allowed_roles );
    } else {
      $query->set( 'role_in', array_intersect( $allowed_roles, $query_allowed_roles ) );
    }

  }

  /**
   * Filter the User Roles select input.
   *
   * @param  array $roles User roles to show in the select input.
   * @return array        Filtered array of roles.
   */
  public static function filter_roles_dropdown( $roles ) {
    $allowed_roles = array( 'subscriber' );
    $filtered_roles = array_intersect_key( $roles, array_flip( $allowed_roles ) );

    return $filtered_roles;
  }

  /**
   * Remove unnecessary columns in Users list table.
   *
   * @param  array $columns List table columns.
   * @return array          Modified list table columns.
   */
  public static function remove_user_list_table_columns( $columns ) {
    unset( $columns['posts'] );
    // WP Cerber columns.
    unset( $columns['cbcc'] ); // Comments.
    unset( $columns['cbla'] ); // Last login.
    unset( $columns['cbfl'] ); // Failed logins.
    unset( $columns['cbdr'] ); // Date registered.

    return $columns;
  }

  /**
   * Remove unnecessary row actions in users list table.
   *
   * @param  array $actions User row actions.
   * @return array          Modified user row actions.
   */
  public static function remove_user_list_table_row_actions( $actions ) {
    unset( $actions['view'] );

    return $actions;
  }

  /**
   * Remove unnecessary user list table view filters.
   *
   * @param  array $views User view filters.
   * @return array        Modified view filters.
   */
  public static function remove_user_list_table_view_filters( $views ) {
    $allowed_views = array(
      'all',
      'subscriber',
      CA_Client_Role::get_role_slug(),
      CA_Site_Manager_Role::get_role_slug(),
    );
    $views = array_intersect_key( $views, array_flip( $allowed_views ) );

    return $views;
  }

  /**
   * Control admin bar visibility.
   *
   */
  public static function subscriber_hide_admin_bar() {
    $current_user = wp_get_current_user();
    if ( ! in_array( 'subscriber', (array) $current_user->roles ) ) {
      return;
    }

    show_admin_bar( false );
  }

  /**
   * Redirect subscribers upon login.
   *
   * @param  string $redirect_to URL to redirect to
   * @param  string $request     Request URL
   * @param  WP_User $user       Loggin-in user
   * @return string              Redirected URL
   */
  public static function subscriber_login_redirect( $redirect_to, $request, $user ) {
    if ( is_a( $user, 'WP_User' ) && in_array( 'subscriber', (array) $user->roles ) ) {
      $redirect_to = home_url();
    }

    return $redirect_to;
  }

} // CA_User_Management
