<?php
/**
 * Client Access Client Role class
 *
 * @author Will Gorham
 * @package WMG\ClientAccess
 * @since 3.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Client user role class.
 *
 * @class CA_Client_Role
 */
class CA_Client_Role extends CA_Role {

  /**
   * User role slug.
   *
   * @var string
   */
  protected static $role_slug = 'ca_client';

  /**
   * User role display name.
   *
   * @var string
   */
  protected static $role_name = 'Client';

} // CA_Client_Role
