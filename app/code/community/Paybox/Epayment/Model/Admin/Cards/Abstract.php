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

abstract class Paybox_Epayment_Model_Admin_Cards_Abstract
{
    public function getConfigPath()
    {
        return 'default/payment/pbxep_'.$this->getConfigNodeName().'/cards';
    }

    public abstract function getConfigNodeName();

    public function toOptionArray()
    {
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
