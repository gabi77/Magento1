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

class Paybox_Epayment_Block_Admin_Presentation extends Mage_Adminhtml_Block_Template {
    protected function _construct() {
    	parent::_construct();

        $config = Mage::getSingleton('pbxep/config');
        $lang = Mage::app()->getLocale();
        if (!empty($lang)) {
            $lang = preg_replace('#_.*$#', '', $lang->getLocaleCode());
        }
        if (!in_array($lang, array('fr', 'en'))) {
            $lang = 'en';
        }
        $this->setTemplate('pbxep/presentation/'.$lang.'.phtml');
    }
}