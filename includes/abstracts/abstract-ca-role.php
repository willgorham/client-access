<?php
/**
 * Abstract Client Access Role class
 *
 * @author Will Gorham
 * @package WMG\ClientAccess
 * @since 1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Abstract user role class.
 *
 * @class CA_Role
 */
abstract class CA_Role {

  /**
   * User role slug.
   *
   * @var string
   */
  protected static $role_slug;

  /**
   * User role display name.
   *
   * @var string
   */
  protected static $role_name;

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
   * Dashboard page redirects to this URL.
   *
   * @var string
   */
  protected static $dashboard_redirect_url = 'edit.php?post_type=page';

  /**
   * Initialize role related features.
   *
   */
  public static function init() {
    // Start everything on init to ensure the current user is set up.
    add_action( 'init', array( get_called_class(), 'init_hooks' ), 1 );

    // Hooks that don't work when tied to 'init'
    add_filter( 'login_redirect', array( get_called_class(), 'login_redirect' ), 10, 3 );
    add_filter( 'user_dashboard_url', array( get_called_class(), 'dashboard_url_redirect' ), 10, 4 );
  }

  /**
   * Hook into actions and filters.
   *
   */
  public static function init_hooks() {

    // The following hooks are only needed if the current user is this role
    if ( ! static::user_has_this_role() ) {
      return;
    }

    add_action( 'wp_before_admin_bar_render', array( get_called_class(), 'remove_admin_bar_nodes' ) );

    // Admin hooks
    add_action( 'admin_enqueue_scripts', array( get_called_class(), 'enqueue_admin_styles' ) );
    add_action( 'admin_head', array( get_called_class(), 'remove_profile_options' ) );
    add_action( 'admin_menu', array( get_called_class(), 'maybe_redirect_dashboard' ) );
    add_action( 'admin_menu', array( get_called_class(), 'manage_admin_menu_items' ) );
    add_action( 'do_meta_boxes', array( get_called_class(), 'remove_page_meta_boxes' ), 10 );
    add_filter( 'screen_options_show_screen', array( get_called_class(), 'remove_admin_screen_options'), 10, 2 );
    add_filter( 'page_row_actions', array( get_called_class(), 'remove_pages_list_row_actions' ), 10, 2 );
    add_filter( 'manage_pages_columns', array( get_called_class(), 'remove_pages_list_columns' ), 10, 1 );
    add_filter( 'edit_page_per_page', array( get_called_class(), 'set_list_table_items_per_page' ), 10, 1 );
    add_filter( 'admin_footer_text', array( get_called_class(), 'remove_admin_footer_text' ), 10, 1 );
    add_filter( 'update_footer', array( get_called_class(), 'remove_admin_footer_version' ), 11 );
    add_filter( 'manage_tablepress_list_columns', array( get_called_class(), 'remove_tablepress_list_columns' ), 10, 1 );
    add_filter( 'appp_push_cpt_args', array( get_called_class(), 'apppresser_notification_cpt_args' ), 10, 1 );
    add_filter( 'map_meta_cap', array( get_called_class(), 'monsterinsights_allow_report_view' ), 11, 4 );
    add_filter( 'tiny_mce_before_init', array( get_called_class(), 'change_MCE_defaults' ), 10, 1 );

    if ( method_exists( get_called_class(), 'role_init_hooks' ) ) {
      static::role_init_hooks();
    }
  }

  /**
   * Update the user role.
   *
   */
  public static function update() {
    // Need to remove role first, otherwise add_role short circuits.
    remove_role( static::$role_slug );
    static::add_role();
  }

  /**
   * Add user role with capabilities.
   *
   */
  public static function add_role() {
    $role = add_role( static::$role_slug, static::$role_name, static::$role_capabilities );
  }

  /**
   * Get the user role object.
   *
   * @return WP_Role user role.
   */
  public static function get_role() {
    return get_role( static::$role_slug );
  }

  /**
   * Get the user role slug.
   *
   * @return string Role slug.
   */
  public static function get_role_slug() {
    return static::$role_slug;
  }

  /**
   * Remove admin menu items.
   *
   */
  public static function manage_admin_menu_items() {
    remove_menu_page( 'index.php' );
    // remove_menu_page( 'upload.php' ); // Remove 'Media' upload menu item
    if ( is_multisite() ) {
      add_menu_page( 'My Sites', 'My Sites', 'edit_others_pages', 'my-sites.php', '', '', 75 );
    }
    static::admin_menu_remove_first_separator();
  }

  /**
   * Redirect WP dashboard access to Pages.
   *
   */
  public static function maybe_redirect_dashboard() {
    if ( preg_match( '#wp-admin/?(index.php)?$#', $_SERVER[ 'REQUEST_URI' ] ) ) {
      wp_safe_redirect( site_url() . '/wp-admin/' . static::$dashboard_redirect_url );
    }
  }

  /**
   * Enqueue admin styles.
   *
   */
  public static function enqueue_admin_styles() {
    $ca = client_access();

    wp_enqueue_style(
      'ca-role-admin-styles',
      $ca->plugin_url() . 'css/admin/ca-role.css',
      array(),
      $ca::VERSION
    );
  }

  /**
   * Remove admin screen options and tab.
   *
   * @param  bool      $show_screen_options Whether to display the screen options tab
   * @param  WP_Screen $screen              Current screen object
   * @return bool                           Filtered display screen options value
   */
  public static function remove_admin_screen_options( $show_screen_options, $screen ) {
    $show_screen_options = false;

    // Remove help tabs while we're at it.
    // Couldn't get this to work on current_screen hook, but it works here.
    $screen->remove_help_tabs();

    return $show_screen_options;
  }

  /**
   * Remove row actions for Pages list table
   *
   * @param  array   $actions Row actions
   * @param  WP_Post $post    Post object for row
   * @return array            Modified actions
   */
  public static function remove_pages_list_row_actions( $actions, $post ) {
    if ( 'page' !== $post->post_type ) {
      return;
    }

    $actions = array();
    return $actions;
  }

  /**
   * Remove columns from pages list table.
   *
   * @param  array $columns Table columns.
   * @return array          Modified table columns.
   */
  public static function remove_pages_list_columns( $columns ) {
    unset( $columns['author'] );
    unset( $columns['comments'] );
    unset( $columns['date'] );

    return $columns;
  }

  /**
   * Remove meta boxes on single Page edit page.
   *
   */
  public static function remove_page_meta_boxes() {
    remove_meta_box( 'revisionsdiv', 'page', 'normal' );
    remove_meta_box( 'commentstatusdiv', 'page', 'normal' );
    remove_meta_box( 'commentsdiv', 'page', 'normal' );
    remove_meta_box( 'slugdiv', 'page', 'normal' );
    remove_meta_box( 'pageparentdiv', 'page', 'side' );
    remove_meta_box( 'authordiv', 'page', 'normal' );
  }

  /**
   * Set number of list table items per page.
   *
   * @param int $per_page Number of items to display.
   */
  public static function set_list_table_items_per_page( $per_page ) {
    return 50;
  }

  /**
   * Remove admin footer WP text.
   *
   * @param  string $text Text for left footer
   * @return string       Filtered text string
   */
  public static function remove_admin_footer_text( $text ) {
    $text = '';

    return $text;
  }

  /**
   * Remove admin footer version text.
   *
   * @param  string $version Version text string
   * @return string          Filtered text string
   */
  public static function remove_admin_footer_version( $version ) {
    $version = '';

    return $version;
  }

  /**
   * Remove options from admin profile edit page.
   *
   */
  public static function remove_profile_options() {
    global $_wp_admin_css_colors;

    $_wp_admin_css_colors = array();
  }

  /**
   * Remove default WP admin bar.
   *
   */
  public static function remove_admin_bar_nodes() {
    if ( ! is_admin_bar_showing() ) {
      return;
    }

    global $wp_admin_bar;

    $wp_admin_bar->remove_node( 'wp-logo' );
    $wp_admin_bar->remove_node( 'my-sites' );
    $wp_admin_bar->remove_node( 'simple-history-view-history' );
    $wp_admin_bar->remove_node( 'new-content' );
    $wp_admin_bar->remove_node( 'edit-profile' );
    $wp_admin_bar->remove_node( 'user-info' );

    if ( ! is_admin() ) {
      $wp_admin_bar->remove_node( 'my-account' );
      $wp_admin_bar->remove_node( 'search' );
      $wp_admin_bar->remove_node( 'edit' );
    }
  }

  /**
   * Remove first item of admin menu if it is a separator.
   *
   */
  protected static function admin_menu_remove_first_separator() {
    global $menu;

    reset( $menu );
    $first_key = key( $menu );
    if ( $menu[$first_key][4] === 'wp-menu-separator' ) {
      unset( $menu[$first_key] );
    }
  }

  /**
   * Check if user has this class's user role.
   *
   * @return bool If the current user has the role.
   */
  public static function user_has_this_role() {
    $current_user = wp_get_current_user();
    if ( in_array( static::$role_slug, (array) $current_user->roles ) ) {
      return true;
    }

    return false;
  }

  /**
   * Remove columns from Tablepress list table
   *
   * @return array $columns Filtered table columns
   */
  public static function remove_tablepress_list_columns( $columns ) {
    unset( $columns['table_author'] );
    unset( $columns['table_description'] );
    unset( $columns['table_last_modified_by'] );

    return $columns;
  }

  /**
   * Filter AppPresser notification CPT args to allow role access
   *
   * @param  array $args Array of CPT args
   * @return array       Filtered args
   */
  public static function apppresser_notification_cpt_args( $args ) {

    $args['capability_type'] = 'page';
    $args['show_in_menu'] = true;
    $args['menu_position'] = 100;
    $args['menu_icon'] = 'dashicons-smartphone';

    return $args;
  }

  /**
   * Allow role to view Monster Insights reports
   *
   * @param  array  $caps    The user's actual capabilities
   * @param  string $cap     Capability name
   * @param  int    $user_id The user ID
   * @param  array  $args    Additional context
   * @return array           User capabilities
   */
  public static function monsterinsights_allow_report_view( $caps, $cap, $user_id, $args ) {

    if ( 'monsterinsights_view_dashboard' === $cap && user_can( $user_id, static::$role_slug) ) {
      $caps = array();
    }

    return $caps;
  }


  /**
   * Redirect user after logging in
   *
   * @param  string           $redirect_to Redirect destination URL
   * @param  string           $request     Requested redirect URL passed as parameter
   * @param  WP_User|WP_Error $user        WP_User if successful login
   * @return string                        Redirect destination URL
   */
  public static function login_redirect( $redirect_to, $request, $user ) {
    // This function is only called when the user is already the appropriate role
    if ( is_multisite() && isset( $user->roles ) && in_array( static::$role_slug, (array) $user->roles ) ) {
      $redirect_to = admin_url( 'my-sites.php' );
    }

    return $redirect_to;
  }


  /**
   * Handle login redirect for users logging into main/non-member sites
   *
   * @param  string $url     Dashbaord URL
   * @param  int    $user_id User ID
   * @param  string $path    Path to be added to the URL
   * @param  string $scheme  URL scheme
   * @return string          Dashboard URL
   */
  public static function dashboard_url_redirect( $url, $user_id, $path, $scheme ) {
    $blogs = get_blogs_of_user( $user_id );

    if ( is_multisite() && ! user_can( $user_id, 'manage_network' ) && ! empty( $blogs ) && '' === $path ) {
      $current_blog_id = get_current_blog_id();
      $user = '';
      if ( $current_blog_id && in_array( $current_blog_id, array_keys( $blogs ) ) ) {
        $user = new WP_User( $user_id, '', $current_blog_id );
      } else {
        $active = get_active_blog_for_user( $user_id );
        if ( $active ) {
          $user = new WP_User( $user_id, '', $active->blog_id );
        }
      }
      if ( is_a( $user, 'WP_User' ) && in_array( static::$role_slug, (array) $user->roles ) ) {
        return get_dashboard_url( $user_id, 'my-sites.php' );
      }
    }

    return $url;
  }


  /**
   * Set TinyMCE Editor defaults.
   *
   * @param  array $settings Editor settings
   * @return array           Editor settings
   */
  public static function change_MCE_defaults( $settings ) {
    // Keep the "kitchen sink" (toggle toolbar) open
    $settings[ 'wordpress_adv_hidden' ] = FALSE;
    return $settings;
  }

} // CA_Role
