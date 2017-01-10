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

class Paybox_Epayment_Model_Admin_Payment_Delays {
	public function toOptionArray() {
		$helper = Mage::helper('pbxep');
		$result = array(
			'1' => array('value' => 1, 'label' => $helper->__('1')),
			'2' => array('value' => 2, 'label' => $helper->__('2')),
			'3' => array('value' => 3, 'label' => $helper->__('3')),
			'4' => array('value' => 4, 'label' => $helper->__('4')),
			'5' => array('value' => 5, 'label' => $helper->__('5')),
			'6' => array('value' => 6, 'label' => $helper->__('6')),
			'7' => array('value' => 7, 'label' => $helper->__('7')),
		);
		return $result;
	}
}