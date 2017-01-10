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

class Paybox_Epayment_Model_Observer_AdminCreateOrder extends Mage_Core_Model_Observer
{
	private static $_oldOrder = null;

	public function onBeforeCreate($observer) {
		$event = $observer->getEvent();
		$session = $event->getSession();

        if ($session->getOrder()->getId()) {
			Paybox_Epayment_Model_Observer_AdminCreateOrder::$_oldOrder = $session->getOrder();
		}
	}

	public function onAfterSubmit($observer) {
		$oldOrder = Paybox_Epayment_Model_Observer_AdminCreateOrder::$_oldOrder;
		if (!is_null($oldOrder)) {
			$order = $observer->getEvent()->getOrder();
			if (!is_null($order)) {
				$payment = $order->getPayment();
				$oldPayment = $oldOrder->getPayment();

				// Payment information
				$payment->setPbxepAction($oldPayment->getPbxepAction());
				$payment->setPbxepAuthorization($oldPayment->getPbxepAuthorization());
				$payment->setPbxepCapture($oldPayment->getPbxepCapture());
				$payment->setPbxepFirstPayment($oldPayment->getPbxepFirstPayment());
				$payment->setPbxepSecondPayment($oldPayment->getPbxepSecondPayment());
				$payment->setPbxepSecondThird($oldPayment->getPbxepSecondPThird());
				$payment->setPbxepDelay($oldPayment->getPbxepDelay());
				$payment->setPbxepSecondPayment($oldPayment->getPbxepSecondPayment());

				// Transactions
				$oldTxns = Mage::getModel('sales/order_payment_transaction')->getCollection();
				$oldTxns->addFilter('payment_id', $oldPayment->getId());
				foreach ($oldTxns as $oldTxn) {
					$payment->setTransactionId($oldTxn->getTxnId());
					$payment->setParentTransactionId($oldTxn->getParentTxnId());
					$txn = $payment->addTransaction($oldTxn->getTxnType());
					$txn->setParentTxnId($oldTxn->getParentTxnId());
					$txn->setIsClosed($oldTxn->getIsClosed());
					$infos = $oldTxn->getAdditionalInformation();
					foreach ($infos as $key => $value) {
						$txn->setAdditionalInformation($key, $value);
					}

					$txn->setOrderPaymentObject($payment);
					$txn->setPaymentId($payment->getId());
					$txn->setOrderId($order->getId());
					$txn->save();
				}

				$payment->save();
			}
        }
	}
}