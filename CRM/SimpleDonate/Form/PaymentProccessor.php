<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.6                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2014                                |
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

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2014
 * $Id$
 *
 */

/**
 * This class generates form components for Component
 */
class CRM_SimpleDonate_Form_PaymentProccessor {
  /**
   * This function sets the values as per selected payment processor.
   */
  public function getadditionalPaymentRes($params, $isTest=NULL){
    if (empty($params['payment_processor'])) {
      return false;
    }
    $paymentProcessors = CRM_Financial_BAO_PaymentProcessor::getPayment($params['payment_processor'], $isTest);
    $proccessorName = $paymentProcessors['payment_processor_type'];
    if (!method_exists(__CLASS__, $proccessorName)) {
      return false;
    }
    $result = self::$proccessorName($params, $paymentProcessors);
    return $result;
  }
  
  /**
   * This function generate and return stripe key.
   *
   */  
  public static function Stripe($params, $paymentProcessors) {
    require_once CRM_Extension_System::singleton()->getMapper()->keyToBasePath('com.drastikbydesign.stripe') . "/packages/stripe-php/lib/Stripe.php";
    if (empty($paymentProcessors['user_name'])) {
      echo json_encode(array('error' => 'No API key provided'));
      CRM_Utils_System::civiExit();  
    }

    Stripe::setApiKey($paymentProcessors['user_name']);
    $token = Stripe_Token::create(array(
      "card" => array(
        "number" => $params["ccNumber"],
        "exp_month" => substr($params['cardExpiry'], 0, 2),
        "exp_year" => "20".substr($params['cardExpiry'], 2, 4),
        "cvc" => $params["cvv2"]
      )
    ));
    return array('stripe_token' => $token['id']);
  }
}
