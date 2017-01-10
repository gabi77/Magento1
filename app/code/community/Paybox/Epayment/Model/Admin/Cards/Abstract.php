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

abstract class Paybox_Epayment_Model_Admin_Cards_Abstract {
	public function getConfigPath() {
		return 'default/payment/pbxep_'.$this->getConfigNodeName().'/cards';
	}

	public abstract function getConfigNodeName();

	public function toOptionArray() {
		$result = array();
		$configPath = $this->getConfigPath();
		$cards = Mage::getConfig()->getNode($configPath)->asArray();
		if (!empty($cards)) {
			$helper = Mage::helper('pbxep');
			foreach ($cards as $code => $card) {
				$result[] = array(
					'label' => $helper->__($card['label']),
					'value' => $code,
				);
			}
		}
		return $result;
	}
}