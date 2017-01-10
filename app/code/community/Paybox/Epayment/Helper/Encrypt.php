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

class Paybox_Epayment_Helper_Encrypt extends Mage_Core_Helper_Abstract {
	/**
	 * You can change this method if you want to use another key than the
	 * one provided by Magento.
	 * @return string Key used for encryption
	 */
	private function _getKey()
	{
		$key = (string)Mage::getConfig()->getNode('global/crypt/key');
		return $key;
	}

	/**
	 * Encrypt $data using 3DES
	 * @param string $data The data to encrypt
	 * @return string The result of encryption
	 * @see Paybox_Epayment_Helper_Encrypt::_getKey()
	 */
	public function encrypt($data)
	{
		if (empty($data)) {
			return '';
		}

		// First encode data to base64 (see end of descrypt)
		$data = base64_encode($data);

		// Prepare mcrypt
		$td = mcrypt_module_open(MCRYPT_3DES, '', MCRYPT_MODE_ECB, '');

		// Prepare key
		$key = $this->_getKey();
		$key = substr($key, 0, 24);
		while (strlen($key) < 24) {
			$key .= substr($key, 0, 24 - strlen($key));
		}

		// Init vector
		$size = mcrypt_enc_get_iv_size($td);
    	$iv = mcrypt_create_iv($size, MCRYPT_RAND);
		mcrypt_generic_init($td, $key, $iv);

		// Encrypt
    	$result = mcrypt_generic($td, $data);

    	// Encode (to avoid data loose when saved to database or
    	// any storage that does not support null chars)
    	$result = base64_encode($result);

    	return $result;
	}

	/**
	 * Decrypt $data using 3DES
	 * @param string $data The data to decrypt
	 * @return string The result of decryption
	 * @see Paybox_Epayment_Helper_Encrypt::_getKey()
	 */
	public function decrypt($data)
	{
		if (empty($data)) {
			return '';
		}

		// First decode encrypted message (see end of encrypt)
		$data = base64_decode($data);

		// Prepare mcrypt
		$td = mcrypt_module_open(MCRYPT_3DES, '', MCRYPT_MODE_ECB, '');

		// Prepare key
		$key = $this->_getKey();
		$key = substr($key, 0, 24);
		while (strlen($key) < 24) {
			$key .= substr($key, 0, 24 - strlen($key));
		}

		// Init vector
		$size = mcrypt_enc_get_iv_size($td);
    	$iv = mcrypt_create_iv($size, MCRYPT_RAND);
		mcrypt_generic_init($td, $key, $iv);

		// Decrypt
    	$result = mdecrypt_generic($td, $data);

    	// Remove any null char (data is base64 encoded so no data loose)
    	$result = rtrim($result, "\0");

    	// Decode data
    	$result = base64_decode($result);

    	return $result;
	}
}