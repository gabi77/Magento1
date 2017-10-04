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
class Paybox_Epayment_Model_Config {

    const SUBSCRIPTION_OFFER1 = 'essential';
    const SUBSCRIPTION_OFFER2 = 'flexible';
    const SUBSCRIPTION_OFFER3 = 'plus';

    private $_store;
    private $_configCache = array();
    private $_configMapping = array(
        'allowedIps' => 'allowedips',
        'environment' => 'environment',
        'debug' => 'debug',
        'hmacAlgo' => 'merchant/hmacalgo',
        'hmacKey' => 'merchant/hmackey',
        'identifier' => 'merchant/identifier',
        'languages' => 'languages',
        'password' => 'merchant/password',
        'rank' => 'merchant/rank',
        'site' => 'merchant/site',
        'subscription' => 'merchant/subscription',
        'kwixoShipping' => 'kwixo/shipping'
    );
    private $_urls = array(
        'system' => array(
            'test' => array(
                'https://preprod-tpeweb.paybox.com/cgi/MYchoix_pagepaiement.cgi'
            ),
            'production' => array(
                'https://tpeweb.paybox.com/cgi/MYchoix_pagepaiement.cgi',
                'https://tpeweb1.paybox.com/cgi/MYchoix_pagepaiement.cgi',
            ),
        ),
        'responsive' => array(
            'test' => array(
                'https://preprod-tpeweb.paybox.com/cgi/FramepagepaiementRWD.cgi'
            ),
            'production' => array(
                'https://tpeweb.paybox.com/cgi/FramepagepaiementRWD.cgi',
                'https://tpeweb1.paybox.com/cgi/FramepagepaiementRWD.cgi',
            ),
        ),
        'kwixo' => array(
            'test' => array(
                'https://preprod-tpeweb.paybox.com/php/'
            ),
            'production' => array(
                'https://tpeweb.paybox.com/php/',
                'https://tpeweb1.paybox.com/php/',
            ),
        ),
        'mobile' => array(
            'test' => array(
                'https://preprod-tpeweb.paybox.com/cgi/MYframepagepaiement_ip.cgi'
            ),
            'production' => array(
                'https://tpeweb.paybox.com/cgi/MYframepagepaiement_ip.cgi',
                'https://tpeweb1.paybox.com/cgi/MYframepagepaiement_ip.cgi',
            ),
        ),
        'direct' => array(
            'test' => array(
                'https://preprod-ppps.paybox.com/PPPS.php'
            ),
            'production' => array(
                'https://ppps.paybox.com/PPPS.php',
                'https://ppps1.paybox.com/PPPS.php',
            ),
        ),
        'resabo' => array(
            'test' => array(
                'https://preprod-tpeweb.paybox.com/cgi-bin/ResAbon.cgi'
            ),
            'production' => array(
                'https://tpeweb.paybox.com/cgi-bin/ResAbon.cgi',
                'https://tpeweb1.paybox.com/cgi-bin/ResAbon.cgi',
            ),
        ),
    );

    public function __call($name, $args) {
        if (preg_match('#^get(.)(.*)$#', $name, $matches)) {
            $prop = strtolower($matches[1]) . $matches[2];
            if (isset($this->_configCache[$prop])) {
                return $this->_configCache[$prop];
            } else if (isset($this->_configMapping[$prop])) {
                $key = 'pbxep/' . $this->_configMapping[$prop];
                $value = $this->_getConfigValue($key);
                $this->_configCache[$prop] = $value;
                return $value;
            }
        } else if (preg_match('#^is(.)(.*)$#', $name, $matches)) {
            $prop = strtolower($matches[1]) . $matches[2];
            if (isset($this->_configCache[$prop])) {
                return $this->_configCache[$prop] == 1;
            } else if (isset($this->_configMapping[$prop])) {
                $key = 'pbxep/' . $this->_configMapping[$prop];
                $value = $this->_getConfigValue($key);
                $this->_configCache[$prop] = $value;
                return $value == 1;
            }
        }
        throw new Exception('No function ' . $name);
    }

    public function getStore() {
        if (is_null($this->_store)) {
            $this->_store = Mage::app()->getStore();
        }
        return $this->_store;
    }

    public function setStore($storeId = null) {
        if (is_null($storeId)) {
            $this->_store = Mage::app()->getStore();
        } else {
            $this->_store = Mage::getModel('core/store')->load($storeId);
        }
        return $this->_store;
    }

    private function _getConfigValue($name) {
        return Mage::getStoreConfig($name, $this->getStore());
    }

    protected function _getUrls($type, $environment = null) {
        if (is_null($environment)) {
            $environment = $this->getEnvironment();
        }
        $environment = strtolower($environment);
        if (isset($this->_urls[$type][$environment])) {
            return $this->_urls[$type][$environment];
        }
        return array();
    }

    public function getHmacKey() {
        $value = $this->_getConfigValue('pbxep/merchant/hmackey');
        return Mage::helper('pbxep/encrypt')->decrypt($value);
    }

    public function getPassword() {
        $value = $this->_getConfigValue('pbxep/merchant/password');
        return Mage::helper('pbxep/encrypt')->decrypt($value);
    }

    public function getPasswordplus() {
        $value = $this->_getConfigValue('pbxep/merchant/passwordplus');
        return Mage::helper('pbxep/encrypt')->decrypt($value);
    }

    public function getSystemUrls($environment = null) {
        return $this->_getUrls('system', $environment);
    }

    public function getResponsiveUrls($environment = null) {
        return $this->_getUrls('responsive', $environment);
    }

    public function getKwixoUrls($environment = null) {
        return $this->_getUrls('kwixo', $environment);
    }

    public function getMobileUrls($environment = null) {
        return $this->_getUrls('mobile', $environment);
    }

    public function getDirectUrls($environment = null) {
        return $this->_getUrls('direct', $environment);
    }

    public function getDefaultNewOrderStatus() {
        return $this->_getConfigValue('pbxep/defaultoption/new_order_status');
    }

    public function getDefaultCapturedStatus() {
        return $this->_getConfigValue('pbxep/defaultoption/payment_captured_status');
    }

    public function getDefaultAuthorizedStatus() {
        return $this->_getConfigValue('pbxep/defaultoption/payment_authorized_status');
    }

    public function getAutomaticInvoice() {
        $value = $this->_getConfigValue('pbxep/automatic_invoice');
        if (is_null($value)) {
            $value = 0;
        }
        return (int) $value;
    }

    public function getCronStatus() {
        return $this->_getConfigValue('pbxep/cron_status');
    }

    public function getCronTime() {
        return $this->_getConfigValue('pbxep/cron_time');
    }
    
    public function getCurrencyConfig() {
        $value = $this->_getConfigValue('pbxep/info/currency');
        if (is_null($value)) {
            $value = 1;
        }
        return (int) $value;
    }

    public function getResponsiveConfig() {
        $value = $this->_getConfigValue('pbxep/info/responsive');
        if (is_null($value)) {
            $value = 0;
        }
        return (int) $value;
    }
    
    public function getResAboUrls($environment = null) {
        return $this->_getUrls('resabo', $environment);
    }

    public function getShowInfoToCustomer() {
        $value = $this->_getConfigValue('pbxep/info_to_customer');
        if (is_null($value)) {
            $value = 1;
        }
        return (int) $value;
    }

    public function getKwixoDefaultCategory() {
        $value = $this->_getConfigValue('pbxep/kwixo/default_category');
        if (is_null($value)) {
            $value = 1;
        }
        return (int) $value;
    }

    public function getKwixoDefaultCarrierType() {
        $value = $this->_getConfigValue('pbxep/kwixo/default_carrier_type');
        if (is_null($value)) {
            $value = 4;
        }
        return (int) $value;
    }

    public function getKwixoDefaultCarrierSpeed() {
        $value = $this->_getConfigValue('pbxep/kwixo/default_carrier_speed');
        if (is_null($value)) {
            $value = 2;
        }
        return (int) $value;
    }

    public function isCronCancelIsActive() {
        return ($this->getCronStatus() == 1) ? true : false;
    }

}
