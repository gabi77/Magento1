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

class Paybox_Epayment_Model_Payment_Threetime extends Paybox_Epayment_Model_Payment_Abstract {

    protected $_code = 'pbxep_threetime';
    protected $_hasCctypes = true;
    protected $_allowRefund = true;
    protected $_3dsAllowed = true;

    public function checkIpnParams(Mage_Sales_Model_Order $order, array $params) {
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

    public function onIPNSuccess(Mage_Sales_Model_Order $order, array $data) {
        $this->logDebug(sprintf('Order %s: Threetime IPN', $order->getIncrementId()));

        $payment = $order->getPayment();

        // Message

        // Create transaction
        $type = Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE;
        $txn = $this->_addPayboxTransaction($order, $type, $data, true, array(
            self::CALL_NUMBER => $data['call'],
            self::TRANSACTION_NUMBER => $data['transaction'],
        ));
        if (is_null($payment->getPbxepFirstPayment())) {
            $this->logDebug(sprintf('Order %s: First payment', $order->getIncrementId()));

            // Message
            $message = 'Payment was authorized and captured by Paybox.';

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
        } else if (is_null($payment->getPbxepSecondPayment())) {
            // Message
            $message = 'Second payment was captured by Paybox.';
            $order->addStatusHistoryComment($message);

            // Additional informations
            $payment->setPbxepSecondPayment(serialize($data));
            $this->logDebug(sprintf('Order %s: %s', $order->getIncrementId(), $message));
        } else if (is_null($payment->getPbxepThirdPayment())) {
            // Message
            $message = 'Third payment was captured by Paybox.';
            $order->addStatusHistoryComment($message);

            // Additional informations
            $payment->setPbxepThirdPayment(serialize($data));
            $this->logDebug(sprintf('Order %s: %s', $order->getIncrementId(), $message));
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
}
