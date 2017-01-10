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

// Initialization
$installer = $this;
$installer->startSetup();

$crypt = Mage::helper('pbxep/encrypt');

$res = Mage::getSingleton('core/resource');
$cnx = $res->getConnection('core-write');
$table = $res->getTableName('core_config_data');

/**
 * Encrypt existing data
 */
// Find raw values
$query = 'select config_id, value from '.$table.' where path in ("pbxep/merchant/hmackey", "pbxep/merchant/password")';
$rows = $cnx->fetchAll($query);

// Process each vlaue
foreach ($rows as $row) {
    $id = $row['config_id'];
    $value = $row['value'];

    // Encrypt the value
    $value = $crypt->encrypt($value);

    // And save to the db
    $cnx->update(
        $table,
        array('value' => $value),
        array('config_id = ?' => $id)
    );
}

/**
 * Add default data as encoded if needed
 */

// HMAC Key
$cfg = new Mage_Core_Model_Config();
$query = 'select 1 from '.$table.' where path = "pbxep/merchant/hmackey" and scope = "default" and scope_id = 0';
$rows = $cnx->fetchAll($query);
if (empty($rows)) {
	$value = '0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF';
	$value = $crypt->encrypt($value);
	$cfg->saveConfig('pbxep/merchant/hmackey', $value);

}

// Password
$cfg = new Mage_Core_Model_Config();
$query = 'select 1 from '.$table.' where path = "pbxep/merchant/password" and scope = "default" and scope_id = 0';
$rows = $cnx->fetchAll($query);
if (empty($rows)) {
	$value = '1999888I';
	$value = $crypt->encrypt($value);
	$cfg->saveConfig('pbxep/merchant/password', $value);

}

// Finalization
$installer->endSetup();