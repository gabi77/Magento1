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
 * @version   3.0.6
 * @author    BM Services <contact@bm-services.com>
 * @copyright 2012-2017 Verifone e-commerce
 * @license   http://opensource.org/licenses/OSL-3.0
 * @link      http://www.paybox.com/
 */

class Paybox_Epayment_Block_Info extends Mage_Payment_Block_Info
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('pbxep/info/default.phtml');
    }

    public function getCreditCards()
    {
        $result = array();
        $cards = $this->getMethod()->getCards();
        $selected = explode(',', $this->getMethod()->getConfigData('cctypes'));
        foreach ($cards as $code => $card) {
            if (in_array($code, $selected)) {
                $result[$code] = $card;
            }
        }

        return $result;
    }

    public function getPayboxData()
    {
        return unserialize($this->getInfo()->getPbxepAuthorization());
    }

    /**
     * @return Paybox_Epayment_Model_Config Verifone e-commerce configuration object
     */
    public function getPayboxConfig()
    {
        return Mage::getSingleton('pbxep/config');
    }

    public function getCardImageUrl()
    {
        $data = $this->getPayboxData();
        $cards = $this->getCreditCards();
        if (isset($cards[$data['cardType']])) {
            $card = $cards[$data['cardType']];
            return $this->getSkinUrl($card['image'], array('_area' => 'frontend'));
        }

        return $this->getSkinUrl('images/pbxep/'.strtolower($data['cardType']).'.45.png', array('_area' => 'frontend'));
    }

    public function getCardImageLabel()
    {
        $data = $this->getPayboxData();
        $cards = $this->getCreditCards();
        if (!isset($cards[$data['cardType']])) {
            return null;
        }

        $card = $cards[$data['cardType']];
        return $card['label'];
    }

    public function isAuthorized()
    {
        $info = $this->getInfo();
        $auth = $info->getPbxepAuthorization();
        return !empty($auth);
    }

    public function canCapture()
    {
        $info = $this->getInfo();
        $capture = $info->getPbxepCapture();
        $config = $this->getPayboxConfig();
        if ($config->getSubscription() == Paybox_Epayment_Model_Config::SUBSCRIPTION_OFFER2 || $config->getSubscription() == Paybox_Epayment_Model_Config::SUBSCRIPTION_OFFER3) {
            if ($info->getPbxepAction() == Paybox_Epayment_Model_Payment_Abstract::PBXACTION_MANUAL) {
                $order = $info->getOrder();
                return empty($capture) && $order->canInvoice();
            }
        }

        return false;
    }

    public function canRefund()
    {
        $info = $this->getInfo();
        $config = $this->getPayboxConfig();

        $order = $info->getOrder();
        $method = $info->getOrder()->getPayment()->getMethodInstance();
        if (!$method->getAllowRefund()) {
            return false;
        }

        $action = $info->getPbxepAction();
        if ($action == 'three-time') {
            $capture = $info->getPbxepFirstPayment();
        } else {
            $capture = $info->getPbxepCapture();
        }

        if ($config->getSubscription() == Paybox_Epayment_Model_Config::SUBSCRIPTION_OFFER2 || $config->getSubscription() == Paybox_Epayment_Model_Config::SUBSCRIPTION_OFFER3) {
            return !empty($capture);
        }

        return false;
    }

    public function getDebitTypeLabel()
    {
        $info = $this->getInfo();
        $action = $info->getPbxepAction();
        if (is_null($action) || ($action == 'three-time')) {
            return null;
        }

        $actions = Mage::getSingleton('pbxep/admin_payment_action')->toOptionArray();
        $result = $actions[$action]['label'];
        if (($info->getPbxepAction() == Paybox_Epayment_Model_Payment_Abstract::PBXACTION_DEFERRED) && (!is_null($info->getPbxepDelay()))) {
            $delays = Mage::getSingleton('pbxep/admin_payment_delays')->toOptionArray();
            $result .= ' (' . $delays[$info->getPbxepDelay()]['label'] . ')';
        }

        return $result;
    }

    public function getShowInfoToCustomer()
    {
        $config = $this->getPayboxConfig();
        return $config->getShowInfoToCustomer() != 0;
    }

    public function getThreeTimeLabels()
    {
        $info = $this->getInfo();
        $action = $info->getPbxepAction();
        if (is_null($action) || ($action != 'three-time')) {
            return null;
        }

        $result = array(
            'first' => $this->__('Not achieved'),
            'second' => $this->__('Not achieved'),
            'third' => $this->__('Not achieved'),
        );

        $data = $info->getPbxepFirstPayment();
        if (!empty($data)) {
            $data = unserialize($data);
            $date = preg_replace('/^([0-9]{2})([0-9]{2})([0-9]{4})$/', '$1/$2/$3', $data['date']);
            $result['first'] = $this->__('%s (%s)', $data['amount'] / 100.0, $date);
        }

        $data = $info->getPbxepSecondPayment();
        if (!empty($data)) {
            $data = unserialize($data);
            $date = preg_replace('/^([0-9]{2})([0-9]{2})([0-9]{4})$/', '$1/$2/$3', $data['date']);
            $result['second'] = $this->__('%s (%s)', $data['amount'] / 100.0, $date);
        }

        $data = $info->getPbxepThirdPayment();
        if (!empty($data)) {
            $data = unserialize($data);
            $date = preg_replace('/^([0-9]{2})([0-9]{2})([0-9]{4})$/', '$1/$2/$3', $data['date']);
            $result['third'] = $this->__('%s (%s)', $data['amount'] / 100.0, $date);
        }

        return $result;
    }

    public function getPartialCaptureUrl()
    {
        $info = $this->getInfo();
        return Mage::helper("adminhtml")->getUrl(
            "*/sales_order_invoice/start", array(
                    'order_id' => $info->getOrder()->getId(),
            )
        );
    }

    public function getCaptureUrl()
    {
        $data = $this->getPayboxData();
        $info = $this->getInfo();
        return Mage::helper("adminhtml")->getUrl(
            "*/pbxep/invoice", array(
                    'order_id' => $info->getOrder()->getId(),
                    'transaction' => $data['transaction'],
            )
        );
    }

    public function getRefundUrl()
    {
        $info = $this->getInfo();
        $order = $info->getOrder();
        $invoices = $order->getInvoiceCollection();
        foreach ($invoices as $invoice) {
            if ($invoice->canRefund()) {
                return Mage::helper("adminhtml")->getUrl(
                    "*/sales_order_creditmemo/new", array(
                            'order_id' => $order->getId(),
                            'invoice_id' => $invoice->getId(),
                    )
                );
            }
        }

        return null;
    }

    public function getRecurringDeleteUrl()
    {
        $data = $this->getPayboxData();
        $info = $this->getInfo();
        return Mage::helper("adminhtml")->getUrl(
            "*/pbxep/recurring", array(
                    'order_id' => $info->getOrder()->getId(),
            )
        );
    }

    public function threeTimeClosed()
    {
        $info = $this->getInfo();
        $action = $info->getPbxepAction();
        if (is_null($action) || ($action != 'three-time')) {
            return true;
        }

        return false;
    }
}
