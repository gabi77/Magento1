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

class Paybox_Epayment_Model_Context {
    private $_order;

    public static function generateToken(Mage_Sales_Model_Order $order) {
        $reference = array();
        $reference[] = $order->getRealOrderId();
        $reference[] = $order->getCustomerName();
        $reference = implode(' - ', $reference);
        return $reference;
    }

    public function getOrder() {
        return $this->_order;
    }


    /**
     * Reference = order id and customer name
     * The data integrity check is provided by the customer name
     */
    public function getToken() {
        return self::generateToken($this->getOrder());
    }

    public function setOrder(Mage_Sales_Model_Order $order) {
        $this->_order = $order;
    }

    public function setToken($reference) {
        $parts = explode(' - ', $reference, 2);
        if (count($parts) < 2) {
            $message = 'Invalid decrypted reference "%s"';
            Mage::throwException(Mage::helper('pbxep')->__($message, $reference));
        }

        // Retrieves order
        $order = Mage::getSingleton('sales/order')->loadByIncrementId($parts[0]);
        if (empty($order)) {
            $message = 'Not existing order id from decrypted reference "%s"';
            Mage::throwException(Mage::helper('pbxep')->__($message, $reference));
        }
        if (is_null($order->getId())) {
            $message = 'Not existing order id from decrypted reference "%s"';
            Mage::throwException(Mage::helper('pbxep')->__($message, $reference));
        }
        if ($order->getCustomerName() != $parts[1]) {
            $message = 'Consistency error on descrypted reference "%s"';
            Mage::throwException(Mage::helper('pbxep')->__($message, $reference));
        }

        $this->_order = $order;
    }
}