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

class Paybox_Epayment_Model_Admin_Subscription {
	public function toOptionArray() {
		return array(
			array('value' => 'essential', 'label' => Mage::helper('pbxep')->__('Paybox System (Essential Pack)')),
			array('value' => 'flexible', 'label' => Mage::helper('pbxep')->__('Paybox System + Paybox Direct (Flexible Pack)')),
			array('value' => 'plus', 'label' => Mage::helper('pbxep')->__('Paybox System + Paybox Direct Plus (Flexible Pack)')),
		);
	}
}