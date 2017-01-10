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

class Paybox_Epayment_Model_Admin_Payment_Same {
	public function toOptionArray() {
		$helper = Mage::helper('pbxep');
        $options = array(
        		array('value' => '', 'label' => $helper->__('')),
				array('value' => 'same', 'label' => $helper->__('Same')),
				array('value' => 'different', 'label' => $helper->__('Different')),
            );
    	return $options;
    }
}