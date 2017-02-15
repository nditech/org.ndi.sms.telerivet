<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.2                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2012                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

// Load the official Telerivet library
require_once 'lib/telerivet-php-client/telerivet.php';

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2012
 * $Id$
 *
 */
class org_ndi_sms_telerivet extends CRM_SMS_Provider {

  /**
   * provider details
   * @var	string
   */
  protected $_providerInfo = array();


  /**
   * We only need one instance of this object. So we use the singleton
   * pattern and cache the instance in this variable
   *
   * @var object
   * @static
   */
  static private $_singleton = array();

  /**
   * Constructor
   *
   * Create and auth a Telerivet session.
   * This is not needed for Telerivet
   *
   * @return void
   */
  function __construct($provider, $skipAuth = TRUE) {
    // Instantiate the Telerivet client
    $this->provider = $provider;
    if(isset($provider['api_params'])){
      $api = new Telerivet_API($provider['api_params']['api_key']);
      $this->tr = $api->initProjectById($provider['api_params']['project_id']);
    }
  }

  /**
   * singleton function used to manage this object
   *
   * @return object
   * @static
   *
   */
  static function &singleton($providerParams = array(), $force = FALSE) {
    $providerID = CRM_Utils_Array::value('provider_id', $providerParams);
    $skipAuth   = $providerID ? FALSE : TRUE;
    $cacheKey   = (int) $providerID;

    if (!isset(self::$_singleton[$cacheKey]) || $force) {
      $provider = array();
      if ($providerID) {
        $provider = CRM_SMS_BAO_Provider::getProviderInfo($providerID);
      }
      self::$_singleton[$cacheKey] = new org_ndi_sms_telerivet($provider, $skipAuth);
    }
    return self::$_singleton[$cacheKey];
  }

  /**
   * Send an SMS Message via the Telerivet API Server
   *
   * @param array the message with a to/from/text
   *
   * @return mixed SID on success or PEAR_Error object
   * @access public
   */
  function send($recipients, $header, $message, $jobID = NULL, $userID = NULL) {

    try{
      $message = $this->tr->sendMessage(array( 'content' => $message, 'to_number' => $header['To'] ));
    }catch(Exception $e) {
      return PEAR::raiseError( $e->getMessage(), $e->getCode(), PEAR_ERROR_RETURN );
    }
    $this->createActivity($message->id, $message, $header, $jobID, $userID);
    return $message->id;
  }

  function callback() {
    // For some reason, when status is defined in the request, the callback will
    // call this function.  Telerivet appears to be returning status = processing
    // as part of its post data so it seems like the right thing to do in any
    // case is call inbound.
  	return $this->inbound();
  }

  function inbound() {
    // $this->provider['api_params']['secret']; // TODO check for secret
    return parent::processInbound( $this->retrieve('from_number', 'String'), $this->retrieve('content', 'String'), NULL, $this->retrieve('id', 'String') );
  }
}
