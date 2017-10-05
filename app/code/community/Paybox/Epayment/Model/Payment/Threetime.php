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

class Paybox_Epayment_Model_Payment_Threetime extends Paybox_Epayment_Model_Payment_Abstract
{
    protected $_code = 'pbxep_threetime';
    protected $_hasCctypes = true;
    protected $_allowRefund = true;
    protected $_3dsAllowed = true;

    public function checkIpnParams(Mage_Sales_Model_Order $order, array $params)
    {
        if (!isset($params['amount'])) {
            $message = $this->__('Missing amount parameter');
            $this->logFatal(sprintf('Order %s: (IPN) %s', $order->getIncrementId(), $message));
            Mage::throwException($message);
        }
        if (!isset($params['transaction'])) {
            $message = $this->__('Missing transaction parameter');
            $this->logFatal(sprintf('Order %s: (IPN) %s', $order->getIncrementId(), $message));
            Mage::throwException($message);
        }
    }

    public function onIPNSuccess(Mage_Sales_Model_Order $order, array $data)
    {
        $this->logDebug(sprintf('Order %s: Threetime IPN', $order->getIncrementId()));

        $payment = $order->getPayment();

        // Create transaction
        $withCapture = $this->getConfigPaymentAction() != Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE;
        $type = $withCapture ?
                Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE :
                Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH;
        $txn = $this->_addPayboxTransaction($order, $type, $data, false, array(
            Paybox_Epayment_Model_Payment_Abstract::CALL_NUMBER => $data['call'],
            Paybox_Epayment_Model_Payment_Abstract::TRANSACTION_NUMBER => $data['transaction'],
        ));
        if (is_null($payment->getPbxepFirstPayment())) {
            $this->logDebug(sprintf('Order %s: First payment', $order->getIncrementId()));

            // Message
            $message = 'Payment was authorized and captured by Verifone e-commerce.';

            // Status
            $status = $this->getConfigPaidStatus();
            $state = Mage_Sales_Model_Order::STATE_PROCESSING;
            $allowedStates = array(
                Mage_Sales_Model_Order::STATE_NEW,
                Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
                Mage_Sales_Model_Order::STATE_PROCESSING,
            );
            $current = $order->getState();
            $message = $this->__($message);
            if (in_array($current, $allowedStates)) {
                $order->setState($state, $status, $message);
            } else {
                $order->addStatusHistoryComment($message);
            }

            // Additional informations
            $payment->setPbxepFirstPayment(serialize($data));
            $payment->setPbxepAuthorization(serialize($data));

            $this->logDebug(sprintf('Order %s: %s', $order->getIncrementId(), $message));

            // Create invoice is needed
            $invoice = $this->_createInvoice($order, $txn);
            // Set status
            if (in_array($current, $allowedStates)) {
                $order->setState($state, $status, $message);
                $this->logDebug(sprintf('Order %s: Change status to %s', $order->getIncrementId(), $status));
            } else {
                $order->addStatusHistoryComment($message);
            }
            $order->save();
        } elseif (is_null($payment->getPbxepSecondPayment())) {
            // Message
            $message = 'Second payment was captured by Verifone e-commerce.';
            $order->addStatusHistoryComment($message);

            // Additional informations
            $payment->setPbxepSecondPayment(serialize($data));
            $this->logDebug(sprintf('Order %s: %s', $order->getIncrementId(), $message));
            $transaction = $this->_addPayboxDirectTransaction($order, Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE, $data, true, array(), $txn);
            $transaction->save();
        } elseif (is_null($payment->getPbxepThirdPayment())) {
            // Message
            $message = 'Third payment was captured by Verifone e-commerce.';
            $order->addStatusHistoryComment($message);

            // Additional informations
            $payment->setPbxepThirdPayment(serialize($data));
            $this->logDebug(sprintf('Order %s: %s', $order->getIncrementId(), $message));

            $transaction = $this->_addPayboxDirectTransaction($order, Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE, $data, true, array(), $txn);
            $transaction->save();
            $txn->closeCapture();
        } else {
            $this->logDebug(sprintf('Order %s: Invalid three-time payment status', $order->getIncrementId()));
            Mage::throwException('Invalid three-time payment status');
        }
        $data['status'] = $message;


        // Associate data to payment
        $payment->setPbxepAction('three-time');

        $transactionSave = Mage::getModel('core/resource_transaction');
        $transactionSave->addObject($payment);
        if (isset($invoice)) {
            $transactionSave->addObject($invoice);
        }
        $transactionSave->save();

        // Client notification if needed
        $order->sendNewOrderEmail();
    }

    public function refund(Varien_Object $payment, $amount)
    {
        echo 'threetime refund';
        die();
        $order = $payment->getOrder();

        // Find capture transaction
        $collection = Mage::getModel('sales/order_payment_transaction')->getCollection()
                ->setOrderFilter($order)
                ->addPaymentIdFilter($payment->getId())
                ->addTxnTypeFilter(Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE);
        if ($collection->getSize() == 0) {
            // If none, error
            Mage::throwException('No payment or capture transaction. Unable to refund.');
        }

        // Transaction found
        $txn = $collection->getFirstItem();

        // Transaction not captured
        if (!$txn->getIsClosed()) {
            Mage::throwException('Payment was not fully captured. Unable to refund.');
        }

        // Call Verifone e-commerce Direct
        $connector = $this->getPaybox();
        $data = $connector->directRefund((float) $amount, $order, $txn);

        // Message
        if ($data['CODEREPONSE'] == '00000') {
            $message = 'Payment was refund by Verifone e-commerce.';
        } else {
            $message = 'Verifone e-commerce direct error (' . $data['CODEREPONSE'] . ': ' . $data['COMMENTAIRE'] . ')';
        }
        $data['status'] = $message;
        $this->logDebug(sprintf('Order %s: %s', $order->getIncrementId(), $message));

        // Transaction
        $transaction = $this->_addPayboxDirectTransaction($order, Mage_Sales_Model_Order_Payment_Transaction::TYPE_REFUND, $data, true, array(), $txn);
        $transaction->save();

        // Avoid automatic transaction creation
        $payment->setSkipTransactionCreation(true);

        // If Verifone e-commerce returned an error, throw an exception
        if ($data['CODEREPONSE'] != '00000') {
            Mage::throwException($message);
        }

        // Add message to history
        $order->addStatusHistoryComment($this->__($message));

        return $this;
    }
}
