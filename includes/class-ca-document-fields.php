<?php
/**
 * Client Access Document Fields class
 *
 * @author Will Gorham
 * @package WMG\ClientAccess
 * @since 1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Users Document Fields class.
 *
 * @class CA_Document_Fields
 */
class CA_Document_Fields {

  /**
   * ACF path.
   *
   * @var string
   */
  private static $acf_path = null;

  /**
   * ACF url.
   *
   * @var string
   */
  private static $acf_url = null;

  /**
   * Initialize document field ACF integration.
   *
   */
  public static function init() {
    $ca = client_access();
    self::set_acf_settings_path( $ca->plugin_path() . 'includes/acf/' );
    self::set_acf_settings_dir( $ca->plugin_url() . 'includes/acf/' );

    add_filter( 'acf/settings/path', array( __CLASS__, 'get_acf_settings_path' ) );
    add_filter( 'acf/settings/dir', array( __CLASS__, 'get_acf_settings_dir' ) );
    add_filter( 'acf/settings/show_admin', '__return_false' );

    include_once( self::get_acf_settings_path() . 'acf.php' );

    self::init_acf_fields();
  }

  /**
   * Get ACF settings path
   *
   */
  public static function get_acf_settings_path() {
    return self::$acf_path;
  }

  /**
   * Get ACF settings dir
   *
   */
  public static function get_acf_settings_dir() {
    return self::$acf_url;
  }

  /**
   * Set ACF settings path for integration
   *
   * @param string $path Included ACF directory path
   */
  public static function set_acf_settings_path( $path ) {
    self::$acf_path = $path;
  }

  /**
   * Set ACF settings dir for integration
   *
   * @param string $dir Included ACF directory URL
   */
  public static function set_acf_settings_dir( $dir ) {
    self::$acf_url = $dir;
  }

  /**
   * Add ACF fields
   *
   */
  private static function init_acf_fields() {
    if( ! function_exists('acf_add_local_field_group') ) {
      return;
    }

    acf_add_local_field_group(array(
      'key' => 'group_5a3a9f70e9bac',
      'title' => 'Client Files',
      'fields' => array(
        array(
          'key' => 'field_5a3a9f7666f99',
          'label' => 'Upload files relevant to this page',
          'name' => 'document',
          'type' => 'repeater',
          'instructions' => 'Make sure to click \'Update\' on the right to save your uploads and notes.',
          'required' => 0,
          'conditional_logic' => 0,
          'wrapper' => array(
            'width' => '',
            'class' => '',
            'id' => '',
          ),
          'collapsed' => '',
          'min' => 0,
          'max' => 0,
          'layout' => 'table',
          'button_label' => 'Add a New File',
          'sub_fields' => array(
            array(
              'key' => 'field_5a3a9f9466f9a',
              'label' => 'File',
              'name' => 'document_file',
              'type' => 'file',
              'instructions' => '',
              'required' => 0,
              'conditional_logic' => 0,
              'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
              ),
              'return_format' => 'url',
              'library' => 'all',
              'min_size' => '',
              'max_size' => '',
              'mime_types' => '',
            ),
            array(
              'key' => 'field_5a3a9ffe66f9b',
              'label' => 'Notes',
              'name' => 'document_notes',
              'type' => 'textarea',
              'instructions' => '',
              'required' => 0,
              'conditional_logic' => 0,
              'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
              ),
              'default_value' => '',
              'placeholder' => 'Add any relevant notes or instructions for this file.',
              'maxlength' => '',
              'rows' => '4',
              'new_lines' => '',
            ),
          ),
        ),
      ),
      'location' => array(
        array(
          array(
            'param' => 'post_type',
            'operator' => '==',
            'value' => 'page',
          ),
        ),
      ),
      'menu_order' => 0,
      'position' => 'normal',
      'style' => 'default',
      'label_placement' => 'top',
      'instruction_placement' => 'label',
      'hide_on_screen' => '',
      'active' => 1,
      'description' => '',
    ));
  }

} // CA_Document_Fields
