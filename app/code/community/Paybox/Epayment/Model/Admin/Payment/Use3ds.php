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

class Paybox_Epayment_Model_Admin_Payment_Use3ds {
	public function toOptionArray() {
		$helper = Mage::helper('pbxep');
        $options = array(
				array('value' => 'always', 'label' => $helper->__('Yes')),
        		array('value' => 'never', 'label' => $helper->__('No')),
				array('value' => 'condition', 'label' => $helper->__('Conditionnal')),
            );
    	return $options;
    }
}