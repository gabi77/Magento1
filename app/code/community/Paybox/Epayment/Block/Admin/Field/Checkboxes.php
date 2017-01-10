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

class Paybox_Epayment_Block_Admin_Field_Checkboxes extends Mage_Adminhtml_Block_System_Config_Form_Field {
    protected function _getOptionHtmlAttributes() {
        return array('type', 'name', 'class', 'style', 'checked', 'onclick', 'onchange', 'disabled');
    }

	protected function _optionToHtml($option, Varien_Data_Form_Element_Abstract $element) {
        $id = $element->getHtmlId().'_'.Mage::helper('core')->escapeHtml($option['value']);

        $html = '<li><input id="'.$id.'"';
        foreach ($this->_getOptionHtmlAttributes() as $attribute) {
            if ($value = $element->getDataUsingMethod($attribute, $option['value'])) {
            	if ($attribute == 'name') {
            		$value .= '[]';
            	}
                $html .= ' '.$attribute.'="'.$value.'"';
            }
        }
        $html .= ' value="'.$option['value'].'" />'
            . ' <label for="'.$id.'">' . $option['label'] . '</label></li>'
            . "\n";
        return $html;
    }

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {
    	$element->setValue(explode(',', $element->getValue()));
    	$values = $element->getValues();

        if (!$values) {
            return '';
        }

        $name = $element->getDataUsingMethod('name', 'NONE');
        $html = '<input type="hidden" name="'.$name.'[]" value="NONE"/>';
        $html  .= '<ul class="checkboxes" id="'.$this->escapeHtml($element->getHtmlId()).'">';
        foreach ($values as $value) {
            $html.= $this->_optionToHtml($value, $element);
        }
        $html .= '</ul>'. $this->getAfterElementHtml();

        return $html;
	}
}