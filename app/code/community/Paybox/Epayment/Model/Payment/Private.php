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

class Paybox_Epayment_Model_Payment_Private extends Paybox_Epayment_Model_Payment_Abstract {
	protected $_code = 'pbxep_private';
	protected $_hasCctypes = true;
    protected $_allowManualDebit = true;
    protected $_allowDeferredDebit = true;
    protected $_allowRefund = true;
}
