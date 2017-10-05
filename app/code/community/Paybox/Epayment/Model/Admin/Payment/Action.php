<?php
/**
 * Paybox Epayment module for Magento
 *
 * Feel free to contact Paybox at support@paybox.com for any
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
 * @version   3.0.4
 * @author    BM Services <contact@bm-services.com>
 * @copyright 2012-2017 Paybox
 * @license   http://opensource.org/licenses/OSL-3.0
 * @link      http://www.paybox.com/
 */

class Paybox_Epayment_Model_Admin_Payment_Action
{
    public function toOptionArray()
    {
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
