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
 * @version   3.0.4
 * @author    BM Services <contact@bm-services.com>
 * @copyright 2012-2017 Verifone e-commerce
 * @license   http://opensource.org/licenses/OSL-3.0
 * @link      http://www.paybox.com/
 */

class Paybox_Epayment_Model_Admin_Payment_Delays
{
    public function toOptionArray()
    {
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
