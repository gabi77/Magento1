<?php
/**
 * Paybox Epayment module for Magento
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * available at : http://opensource.org/licenses/osl-3.0.php
 *
 * @package    Paybox_Epayment
 * @copyright  Copyright (c) 2013-2014 Paybox
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Order Statuses source model
 */
class Paybox_Epayment_Model_Admin_Order_Status_Autocapture extends Mage_Adminhtml_Model_System_Config_Source_Order_Status {
	protected $_stateStatuses = array(
        Mage_Sales_Model_Order::STATE_NEW,
        Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
	);

    public function toOptionArray() {
    	$options = parent::toOptionArray();
    	$options[0] = array(
    		'value' => '',
    		'label' => Mage::helper('pbxep')->__('Manual capture only'),
    	);
    	return $options;
    }
}