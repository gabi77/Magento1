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

class Paybox_Epayment_Block_Admin_Kwixo_Shipping extends Mage_Adminhtml_Block_System_Config_Form_Field {
    protected function _renderSelect($name, array $values, $current = null) {
        $html = '<select name="'.$this->escapeHtml($name).'">';
        foreach ($values as $value) {
            $html .= '<option value="'.((int)$value['value']).'"';
            if ($value['value'] == $current) {
                $html .= ' selected="selected"';
            }
            $html .= '>'.$this->escapeHtml($value['label']).'</option>';
        }
        $html .= '</select>';
        return $html;
    }

    /**
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element) {
        $html = '<td colspan="4">';
        $html .= '<table id="'.$this->escapeHtml($element->getHtmlId()).' width="98%"><tr>';
        $html .= '<th width="120px">'.$this->__('Code').'</th>';
        $html .= '<th width="240px">'.$this->__('Nom').'</th>';
        $html .= '<th>'.$this->__('Type FIA-NET').'</th>';
        $html .= '<th>'.$this->__('Rapidité').'</th>';
        $html .= '<th>'.$this->__('Transporteur').'</th>';
        $html .= '</tr></thead><tbody>';

        $config = Mage::getModel('pbxep/config');
        $defaultType = $config->getKwixoDefaultCarrierType();
        $defaultSpeed = $config->getKwixoDefaultCarrierSpeed();

        $values = $element->getValue();
        $shippingTypes = Mage::getModel('pbxep/admin_fianet_shippingTypes')->toOptionArray();
        $deliverySpeeds = Mage::getModel('pbxep/admin_fianet_deliverySpeeds')->toOptionArray();
        $carriers = Mage::getModel('shipping/config')->getAllCarriers();

        foreach ($carriers as $code => $carrier) {
            $title = Mage::getStoreConfig('carriers/'.$code.'/title');
            $type = $defaultType;
            $speed = $defaultSpeed;
            $name = $title;
            if (isset($values[$code]['type'])) {
                $type = (int)$values[$code]['type'];
            }
            if (isset($values[$code]['speed'])) {
                $speed = (int)$values[$code]['speed'];
            }
            if (isset($values[$code]['name'])) {
                $name = (string)$values[$code]['name'];
            }

            $base = $element->getName().'['.$code.']';

            $html .= '<tr>';
            $html .= '<td>'.$this->escapeHtml($code).'</td>';
            $html .= '<td>'.$this->escapeHtml($title).'</td>';
            $html .= '<td>'.$this->_renderSelect($base.'[type]', $shippingTypes, $type).'</td>';
            $html .= '<td>'.$this->_renderSelect($base.'[speed]', $deliverySpeeds, $speed).'</td>';
            $html .= '<td><input type="text" name="'.$base.'[name]" value="'.$this->escapeHtml($name).'"/></td>';
            $html .= '</tr>';

        }
        $html .= '</tbody></table>';
        $html .= '</td>';
        return $this->_decorateRowHtml($element, $html);
    }

    /**
     * Decorate field row html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @param string $html
     * @return string
     */
    protected function _decorateRowHtml($element, $html) {
        return '<tr id="row_' . $element->getHtmlId() . '">' . $html . '</tr>';
    }

}
