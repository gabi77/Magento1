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

class Paybox_Epayment_Model_Admin_Fianet_Categories extends Mage_Eav_Model_Entity_Attribute_Source_Abstract {

    public function getAllOptions() {
        if (is_null($this->_options)) {
            $helper = Mage::helper('pbxep');
            $kwixo = Mage::getSingleton('pbxep/kwixo');
            $this->_options = array(
                array('value' => null, 'label' => ''),
            );
            foreach ($kwixo->getCategories() as $value => $label) {
                $this->_options[] = array(
                    'value' => $value,
                    'label' => $helper->__($label),
                );
            }
        }
        return $this->_options;
    }

    public function toOptionArray() {
        return $this->getAllOptions();
    }

}
