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

class Paybox_Epayment_Model_Kwixo {
	// category defined by Receive&Pay
	private $_categories = array(
		1 => 'Alimentation & gastronomie',
		2 => 'Auto & moto',
		3 => 'Culture & divertissements',
		4 => 'Maison & jardin',
		5 => 'Electroménager',
		6 => 'Enchères et achats groupés',
		7 => 'Fleurs & cadeaux',
		8 => 'Informatique & logiciels',
		9 => 'Santé & beauté',
		10 => 'Services aux particuliers',
		11 => 'Services aux professionnels',
		12 => 'Sport',
		13 => 'Vêtements & accessoires',
		14 => 'Voyage & tourisme',
		15 => 'Hifi, photo & vidéos',
		16 => 'Téléphonie & communication',
		17 => 'Bijoux et métaux précieux',
		18 => 'Articles et accessoires pour bébé',
		19 => 'Sonorisation & lumière'
	);

	private $_carrierType = array(
		//1 => 'Retrait de la marchandise chez le marchand',
		//2 => 'Utilisation d\'un réseau de points-retrait tiers (type kiala, alveol, etc.)',
		//3 => 'Retrait dans un aéroport, une gare ou une agence de voyage',
		4 => 'Transporteur (La Poste, Colissimo, UPS, DHL... ou tout transporteur privé)',
		5 => 'Emission d’un billet électronique, téléchargements'
	);

	private function _getAddressParams(Mage_Sales_Model_Order_Address $address, $prefix) {
        $country = $address->getCountry();
        $country = Mage::getModel('directory/country')->loadByCode($country);

		$values = array(
			$prefix.'CIVILITY' => 'monsieur',
			$prefix.'NAME_FIRST' => $this->cleanupUpString($address->getFirstname()),
			$prefix.'NAME_LAST' => $this->cleanupUpString($address->getLastname()),
			$prefix.'OFFICE' => $this->cleanupUpString($address->getCompany()),
			$prefix.'MOBILE' => $this->cleanUpPhone($address->getTelephone()),
			$prefix.'HOME' => $this->cleanUpPhone($address->getTelephone()),
        	$prefix.'EMAIL' => $address->getEmail(),
			$prefix.'STREET_LINE_1' => $this->cleanupUpString($address->getStreet(1)),
			$prefix.'POSTALCODE' => $address->getPostcode(),
			$prefix.'CITY' => $this->cleanupUpString($address->getCity()),
			$prefix.'COUNTRY' => $this->cleanupUpString($country->getName()),
		);

		if (!is_null($address->getFax())) {
            $values[$prefix.'FAX'] = $this->cleanUpPhone($address->getFax());
        }

        $street = $address->getStreet(2);
        if (!empty($street)) {
            $values[$prefix.'STREET_LINE_2'] = $this->cleanupUpString($street);
        }

        return $values;
	}

    protected function _getFianetCategory(Mage_Catalog_Model_Product $product) {
        $result = $product->getFianetCategory();
        if (!is_null($result)) {
            return $result;
        }

        $categories = $product->getCategoryCollection();
        foreach ($categories as $category) {
            $result = $category->getFianetCategory();
            if (!is_null($result)) {
                return $result;
            }

            $parent = $category->getParentCategory();
            while (!is_null($parent)) {
                $result = $category->getFianetCategory();
                $propagate = $category->getFianetApplyToSubs();
                if (!is_null($result)) {
                    if ($propagate == 1) {
                        return $result;
                    } else {
                        break;
                    }
                }
            }
        }

        $config = $this->getConfig();
        return $config->getKwixoDefaultCategory();
    }

    private function _getOrderHistory(Mage_Sales_Model_Order $order) {
        $orders = Mage::getModel('sales/order')->getCollection()
                ->addAttributeToSelect('grand_total')
                ->addAttributeToSelect('created_at')
                ->addFieldToFilter('status', array('in' => array(
                        'processing',
                        'complete',
                        'closed',
            )))
                ->addFieldToFilter('customer_id', $order->getCustomerId());
        $sum = 0;
        $first = new DateTime();
        $last = DateTime::createFromFormat('Y-m-d', '1970-01-01');
        $cnt = 0;
        foreach ($orders as $previous) {
            ++$cnt;
            $sum += $previous->getGrandTotal();
            $date = DateTime::createFromFormat('Y-m-d H:i:s', $previous->getCreatedAt());
            if ($date < $first) {
                $first = $date;
            }
            if ($date > $last) {
                $last = $date;
            }
        }

        $values = array();
        $values['PBX_BILLTO_NB_PAYMENTS'] = $cnt;
        if ($cnt > 0) {
            $values['PBX_BILLTO_SUM_AMOUNTS'] = (int) ($sum * 100);
            $values['PBX_BILLTO_DATE_FIRST'] = $first->format('Y-m-d') . 'T' . $first->format('H:i:s');
            $values['PBX_BILLTO_DATE_LAST'] = $last->format('Y-m-d') . 'T' . $last->format('H:i:s');
        }
        return $values;
	}

	private function _getProductDetails(Mage_Sales_Model_Order $order) {
		$config = $this->getconfig();

		$amountScale = $this->getPaybox()->getCurrencyScale($order);

        $items = $order->getAllVisibleItems();
        $products = array();
        foreach ($items as $item) {
            $sku = $item->getSku();
            $name = $item->getName();
            $type = $this->_getFianetCategory($item);
            if (empty($type)) {
                $type = $config->getKwixoDefaultCategory();
            }
            if (empty($type)) {
            	$type = 1;
            }
            $products[] = array(
                'reference' => $this->cleanupUpString($sku),
                'type' => (string)$type,
                'label' => $this->cleanupUpString($name),
                'quantity' => (string)(int)$item->getQtyOrdered(),
                'unitprice' => (string)(int)round(floatval($item->getPrice()) * $amountScale),
            );
        }

        return $products;
	}

	public function buildKwixoParams(Mage_Sales_Model_Order $order, array $values) {
		$config = $this->getconfig();
		$shipping = unserialize($config->getKwixoShipping());
		$carrier = $order->getShippingCarrier();
		if (empty($carrier)) {
			$shipping = array(
				'code' => '',
				'delay' => 3,
				'name' => '',
				'speed' => $config->getKwixoDefaultCarrierSpeed(),
				'type' => $config->getKwixoDefaultCarrierType(),
			);
		}
		else if (!isset($shipping[$carrier->getCarrierCode()])) {
			$shipping = array(
				'code' => $carrier->getCarrierCode(),
				'delay' => 3,
				'name' => $carrier->getConfigData('title'),
				'speed' => $config->getKwixoDefaultCarrierSpeed(),
				'type' => $config->getKwixoDefaultCarrierType(),
			);
		}
		else {
			$shipping = $shipping[$carrier->getCarrierCode()];
			if (empty($shipping['name'])) {
				$shipping['name'] = $carrier->getConfigData('title');
			}
			$shipping['code'] = $carrier->getCarrierCode();
			$shipping['delay'] = (int)$shipping['delay'];
			if (empty($shipping['delay'])) {
				$shipping['delay'] = 3;
			}
		}

		// Date and delay
		$orderDate = date("Y-m-d H:i:s");
		$deliveryDate = mktime(0, 0, 0, date('m'), date('d') + $shipping['delay']);
		$deliveryDate = date('Y-m-d', $deliveryDate);

		// Billing information
		$address = $order->getBillingAddress();
        $others = $this->_getAddressParams($address, 'PBX_BILLTO_');
        $values = array_merge($values, $others);

		if ($shipping['type'] == 4) {
			// Shipping information
			$address = $order->getShippingAddress();
	        $others = $this->_getAddressParams($address, 'PBX_SHIPTO_');
	        $values = array_merge($values, $others);
	    }

		// Carrier information
		$values['PBX_SHIPTO_SHIPPER_NAME']	= $this->cleanupUpString($shipping['name']);
		$values['PBX_SHIPTO_SHIPPER_ID']	= $this->cleanupUpString($shipping['code']);
		$values['PBX_SHIPTO_SHIPPER_SIGN']	= $this->cleanupUpString($shipping['name']);
		$values['PBX_SHIPTO_DELIVERY_TYPE']	= $shipping['type'];
		$values['PBX_SHIPTO_DELIVERY_SPEED']= $shipping['speed'];
		$values['PBX_DELIVERY_DATE']		= $deliveryDate;

		// Order information
		$values['PBX_ORDER_DATE']			= $orderDate;

		// Order history
        $values = array_merge($values, $this->_getOrderHistory($order));

        // Product details
		$values['PBX_PRODUCT_DETAILS'] = json_encode($this->_getProductDetails($order));

		return $values;
	}

	public function cleanUpPhone($text) {
		$text = preg_replace('#[^0-9]+#', '', $text);
		if (preg_match('#^33[1-9][0-9]{8}$#', $text, $matches)) {
			return '+'.$text;
		}
		else if (preg_match('#^0[1-9][0-9]{8}$#', $text, $matches)) {
			return $text;
		}
		throw new \Exception('Invalid phone number "'.$text.'"');
	}

	public function cleanupUpString($str) {
		// TODO: Code to review
		if (function_exists('mb_strtolower'))
			$str = mb_strtolower($str, 'utf-8');

		$str = trim($str);

		// Remove all non-whitelist chars.
		$str = preg_replace('/[^a-zA-Z0-9\s\'\:\/\[\]-\pL]/u', '', $str);
		$str = preg_replace('/[\s\'\:\/\[\]-]+/', ' ', $str);

		// If it was not possible to lowercase the string with mb_strtolower, we do it after the transformations.
		// This way we lose fewer special chars.
		if (!function_exists('mb_strtolower'))
			$str = strtolower($str);

		return $str;
	}

	public function getCategories() {
		return $this->_categories;
	}

	public function getCarrierType() {
		return $this->_carrierType;
	}

    public function getConfig() {
        return Mage::getSingleton('pbxep/config');
    }

    public function getPaybox() {
        return Mage::getSingleton('pbxep/paybox');
    }
}