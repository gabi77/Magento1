<?php

/**
 * Verifone e-commerce Epayment module for Magento
 *
 * Feel free to contact Verifone e-commerce at support@paybox.com for any
 * question.
 *
 * LICENSE: This source file is subject to the version 3.0 of the Open
 * Software License (OSL-3.0) that is available through the world-wide-web
 * at the following URI: http://opensource.org/licenses/OSL-3.0. If
 * you did not receive a copy of the OSL-3.0 license and are unable
 * to obtain it through the web, please send a note to
 * support@paybox.com so we can mail you a copy immediately.
 *
 *
 * @version   3.0.5
 * @author    BM Services <contact@bm-services.com>
 * @copyright 2012-2017 Verifone e-commerce
 * @license   http://opensource.org/licenses/OSL-3.0
 * @link      http://www.paybox.com/
 */
class Paybox_Epayment_Block_Redirect extends Mage_Page_Block_Html
{
    public function getFormFields()
    {
        $order = Mage::registry('pbxep/order');
        $payment = $order->getPayment()->getMethodInstance();
        $cntr = Mage::getSingleton('pbxep/paybox');
        $values = $cntr->buildSystemParams($order, $payment);
        $cntr->logDebug(sprintf('Values: %s', json_encode($values)));
        return $values;
    }

    public function getInputType()
    {
        $config = Mage::getSingleton('pbxep/config');
        if ($config->isDebug()) {
            return 'text';
        }
        return 'hidden';
    }

    public function getKwixoUrl()
    {
        $paybox = Mage::getSingleton('pbxep/paybox');
        $urls = $paybox->getConfig()->getKwixoUrls();
        return $paybox->checkUrls($urls);
    }

    public function getAncvUrl()
    {
        $paybox = Mage::getSingleton('pbxep/paybox');
        $urls = $paybox->getConfig()->getAncvUrls();
        return $paybox->checkUrls($urls);
    }

    public function getMobileUrl()
    {
        $paybox = Mage::getSingleton('pbxep/paybox');
        $urls = $paybox->getConfig()->getMobileUrls();
        return $paybox->checkUrls($urls);
    }

    public function getSystemUrl()
    {
        $paybox = Mage::getSingleton('pbxep/paybox');
        $urls = $paybox->getConfig()->getSystemUrls();
        return $paybox->checkUrls($urls);
    }

    public function getResponsiveUrl()
    {
        $paybox = Mage::getSingleton('pbxep/paybox');
        $urls = $paybox->getConfig()->getResponsiveUrls();
        return $paybox->checkUrls($urls);
    }
}
