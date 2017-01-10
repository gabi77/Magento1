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

class Paybox_Epayment_PaymentController extends Mage_Core_Controller_Front_Action {

    private function _404() {
        $this->_forward('defaultNoRoute');
    }

    private function _loadQuoteFromOrder(Mage_Sales_Model_Order $order) {
        $quoteId = $order->getQuoteId();

        // Retrieves quote
        $quote = Mage::getSingleton('sales/quote')->load($quoteId);
        if (empty($quote) || is_null($quote->getId())) {
            $message = 'Not existing quote id associated with the order %d';
            Mage::throwException(Mage::helper('pbxep')->__($message, $order->getId()));
        }

        return $quote;
    }

    private function _getOrderFromParams(array $params) {
        // Retrieves order
        $paybox = $this->getPaybox();
        $order = $paybox->untokenizeOrder($params['reference']);
        if (is_null($order) || is_null($order->getId())) {
            return null;
        }
        return $order;
    }

    public function cancelAction() {
        try {
            $session = $this->getSession();
            $paybox = $this->getPaybox();

            // Retrieves params
            $params = $paybox->getParams();
            if ($params === false) {
                return $this->_404();
            }

            // Load order
            $order = $this->_getOrderFromParams($params);
            if (is_null($order) || is_null($order->getId())) {
                return $this->_404();
            }

            // Payment method
            $order->getPayment()->getMethodInstance()->onPaymentCanceled($order);

            // Set quote to active
            $this->_loadQuoteFromOrder($order)->setIsActive(true)->save();

            // Cleanup
            $session->unsCurrentPbxepOrderId();

            $message = sprintf('Order %d: Payment was canceled by user on Paybox payment page.', $order->getIncrementId());
            $this->logDebug($message);

            $message = $this->__('Payment canceled by user');
            $session->addError($message);
        }
        catch (Exception $e) {
            $this->logDebug(sprintf('cancelAction: %s', $e->getMessage()));
        }

        // Redirect to cart
        $this->_redirect('checkout/cart');
    }

    public function failedAction() {
        try {
            $session = $this->getSession();
            $paybox = $this->getPaybox();

            // Retrieves params
            $params = $paybox->getParams(false, false);
            if ($params === false) {
                return $this->_404();
            }

            // Load order
            $order = $this->_getOrderFromParams($params);
            if (is_null($order) || is_null($order->getId())) {
                return $this->_404();
            }

            // Payment method
            $order->getPayment()->getMethodInstance()->onPaymentFailed($order);

            // Set quote to active
            $this->_loadQuoteFromOrder($order)->setIsActive(true)->save();

            // Cleanup
            $session->unsCurrentPbxepOrderId();

            $message = sprintf('Order %d: Customer is back from Paybox payment page. Payment refused by Paybox (%d).', $order->getIncrementId(), $params['error']);
            $this->logDebug($message);

            $message = $this->__('Payment refused by Paybox');
            $session->addError($message);
        }
        catch (Exception $e) {
            $this->logDebug(sprintf('failureAction: %s', $e->getMessage()));
        }

        // Redirect to cart
        $this->_redirect('checkout/cart');
    }

    public function getConfig() {
        return Mage::getSingleton('pbxep/config');
    }

    public function getPaybox() {
        return Mage::getSingleton('pbxep/paybox');
    }

    public function getSession() {
        return Mage::getSingleton('checkout/session');
    }

    public function ipnAction() {
        try {
            $paybox = $this->getPaybox();

            // Retrieves params
            $params = $paybox->getParams(true);
            if ($params === false) {
                return $this->_404();
            }

            // Load order
            $order = $this->_getOrderFromParams($params);
            if (is_null($order) || is_null($order->getId())) {
                return $this->_404();
            }

            // IP not allowed
            // $config = $this->getConfig();
            // $allowedIps = explode(',', $config->getAllowedIps());
            // $currentIp = Mage::helper('core/http')->getRemoteAddr();
            // if (!in_array($currentIp, $allowedIps)) {
            //     $message = $this->__('IPN call from %s not allowed.', $currentIp);
            //     $order->addStatusHistoryComment($message);
            //     $order->save();
            //     $this->logFatal(sprintf('Order %s: (IPN) %s', $order->getIncrementId(), $message));
            //     $message = 'Access denied to %s';
            //     Mage::throwException($this->__($message, $currentIp));
            // }

            // Call payment method
            $method = $order->getPayment()->getMethodInstance();
            $res = $method->onIPNCalled($order, $params);

            if ($res) {
                echo 'Done.';
            }
            else {
                echo 'Already done.';
            }
        }
        catch (Exception $e) {
            $message = sprintf('(IPN) Exception %s (%s %d).', $e->getMessage(), $e->getFile(), $e->getLine());
            $this->logFatal($message);
            header('Status: 500 Error', true, 500);
            echo $e->getMessage();
        }
    }

    public function logDebug($message) {
        Mage::log($message, Zend_Log::DEBUG, 'paybox-epayment.log');
    }

    public function logWarning($message) {
        Mage::log($message, Zend_Log::WARN, 'paybox-epayment.log');
    }

    public function logError($message) {
        Mage::log($message, Zend_Log::ERR, 'paybox-epayment.log');
    }

    public function logFatal($message) {
        Mage::log($message, Zend_Log::ALERT, 'paybox-epayment.log');
    }

    public function redirectAction() {
        // Retrieves order id
        $session = $this->getSession();
        $orderId = $session->getLastRealOrderId();

        // If none, try previously saved
        if (is_null($orderId)) {
            $orderId = $session->getCurrentPbxepOrderId();
        }

        // If none, 404
        if (is_null($orderId)) {
            return $this->_404();
        }

        // Load order
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
        if (is_null($order) || is_null($order->getId())) {
            $session->unsCurrentPbxepOrderId();
            return $this->_404();
        }

        // Check order status
        $state = $order->getState();
        if ($state != Mage_Sales_Model_Order::STATE_NEW) {
            $session->unsCurrentPbxepOrderId();
            return $this->_404();
        }

        // Save id
        $session->setCurrentPbxepOrderId($orderId);

        // Keep track of order for security check
        $orders = $session->getPbxepOrders();
        if (is_null($orders)) {
            $orders = array();
        }
        $orders[Mage::helper('core')->encrypt($orderId)] = true;
        $session->setPbxepOrders($orders);

        // Payment method
        $order->getPayment()->getMethodInstance()->onPaymentRedirect($order);

        // Render form
        Mage::register('pbxep/order', $order);
        $this->loadLayout();
        $this->renderLayout();
    }

    public function successAction() {
        try {
            $session = $this->getSession();
            $paybox = $this->getPaybox();

            // Retrieves params
            $params = $paybox->getParams(false, false);
            if ($params === false) {
                return $this->_404();
            }

            // Load order
            $order = $this->_getOrderFromParams($params);
            if (is_null($order) || is_null($order->getId())) {
                return $this->_404();
            }

            // Payment method
            $order->getPayment()->getMethodInstance()->onPaymentSuccess($order, $params);

            // Cleanup
            $session->unsCurrentPbxepOrderId();

            $message = sprintf('Order %s: Customer is back from Paybox payment page. Payment success.', $order->getIncrementId());
            $this->logDebug($message);

            // Redirect to success page
            $this->_redirect('checkout/onepage/success');
            return;
        }
        catch (Exception $e) {
            $this->logDebug(sprintf('successAction: %s', $e->getMessage()));
        }

        $this->_redirect('checkout/cart');
    }
}
