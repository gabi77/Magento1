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

class Paybox_Epayment_Model_Admin_Payment_Action {
	public function toOptionArray() {
		$immediate = array(
			'value' => 'immediate',
			'label' => Mage::helper('pbxep')->__('Paid Immediatly')
		);
		$deferred = array(
			'value' => 'deferred',
			'label' => Mage::helper('pbxep')->__('Defered payment')
		);
		$manual = array(
			'value' => 'manual',
			'label' => Mage::helper('pbxep')->__('Paid shipping')
		);

		$config = Mage::getSingleton('pbxep/config');
		if ($config->getSubscription() == Paybox_Epayment_Model_Config::SUBSCRIPTION_OFFER1) {
			$manual['disabled'] = 'disabled';
		}

		return array(
			$immediate['value'] => $immediate,
			$deferred['value'] => $deferred,
			$manual['value'] => $manual,
		);
	}
}