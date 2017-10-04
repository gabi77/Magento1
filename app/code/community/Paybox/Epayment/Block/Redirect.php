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
class Paybox_Epayment_Block_Redirect extends Mage_Page_Block_Html {

    public function getFormFields() {
        $order = Mage::registry('pbxep/order');
        $payment = $order->getPayment()->getMethodInstance();
        $cntr = Mage::getSingleton('pbxep/paybox');
        $values = $cntr->buildSystemParams($order, $payment);
        $cntr->logDebug(sprintf('Values: %s', json_encode($values)));
        return $values;
    }

    public function getInputType() {
        $config = Mage::getSingleton('pbxep/config');
        if ($config->isDebug()) {
            return 'text';
        }
        return 'hidden';
    }

    public function getKwixoUrl() {
        $paybox = Mage::getSingleton('pbxep/paybox');
        $urls = $paybox->getConfig()->getKwixoUrls();
        return $paybox->checkUrls($urls);
    }

    public function getMobileUrl() {
        $paybox = Mage::getSingleton('pbxep/paybox');
        $urls = $paybox->getConfig()->getMobileUrls();
        return $paybox->checkUrls($urls);
    }

    public function getSystemUrl() {
        $paybox = Mage::getSingleton('pbxep/paybox');
        $urls = $paybox->getConfig()->getSystemUrls();
        return $paybox->checkUrls($urls);
    }

    public function getResponsiveUrl() {
        $paybox = Mage::getSingleton('pbxep/paybox');
        $urls = $paybox->getConfig()->getResponsiveUrls();
        return $paybox->checkUrls($urls);
    }

}
