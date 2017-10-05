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

class Paybox_Epayment_Adminhtml_PbxepController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Fired when an administrator click the total payment on paybox box
     * @return type
     */
    public function invoiceAction()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($orderId);
        $data = $this->getRequest()->getParams();

        $payment = $order->getPayment();
        $method = $payment->getMethodInstance();

        $result = $method->makeCapture($order);

        if (!$result) {
            Mage::getSingleton('adminhtml/session')->setCommentText($this->__('Unable to create an invoice.'));
        }

        $this->_redirect('*/sales_order/view', array('order_id' => $orderId));
    }

    public function recurringAction()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($orderId);

        $payment = $order->getPayment();
        $method = $payment->getMethodInstance();

        $result = $method->deleteRecurringPayment($order);

        if (!$result) {
            Mage::getSingleton('adminhtml/session')->setCommentText($this->__('Unable to cancel recurring payment.'));
        }

        $this->_redirect('*/sales_order/view', array('order_id' => $orderId));
    }
}
