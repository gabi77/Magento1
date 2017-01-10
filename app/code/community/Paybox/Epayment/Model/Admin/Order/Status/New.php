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
class Paybox_Epayment_Model_Admin_Order_Status_New extends Mage_Adminhtml_Model_System_Config_Source_Order_Status {
	protected $_stateStatuses = Mage_Sales_Model_Order::STATE_NEW;
}
