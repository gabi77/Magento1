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

class Paybox_Epayment_Block_Admin_Field_Select extends Mage_Adminhtml_Block_System_Config_Form_Field {
    protected function _getOptionHtmlAttributes() {
        return array('type', 'name', 'class', 'style', 'checked', 'onclick', 'onchange', 'disabled');
    }

	protected function _optionToHtml($option, $selected) {
        if (is_array($option['value'])) {
            $html ='<optgroup label="'.$option['label'].'">'."\n";
            foreach ($option['value'] as $groupItem) {
                $html .= $this->_optionToHtml($groupItem, $selected);
            }
            $html .='</optgroup>'."\n";
        }
        else {
            $html = '<option value="'.Mage::helper('core')->escapeHtml($option['value']).'"';
            $html.= isset($option['title']) ? 'title="'.Mage::helper('core')->escapeHtml($option['title']).'"' : '';
            $html.= isset($option['style']) ? 'style="'.$option['style'].'"' : '';
            $html.= isset($option['disabled']) ? 'disabled="disabled"' : '';
            if (in_array($option['value'], $selected)) {
                $html.= ' selected="selected"';
            }
            $html.= '>'.Mage::helper('core')->escapeHtml($option['label']). '</option>'."\n";
        }
        return $html;
    }

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {
        $element->addClass('select');
        $html = '<select id="'.$element->getHtmlId().'" name="'.
            $element->getName().'" '.
            $this->serialize($element->getHtmlAttributes()).'>'."\n";

        $value = $element->getValue();
        if (!is_array($value)) {
            $value = array($value);
        }

        if ($values = $element->getValues()) {
            foreach ($values as $key => $option) {
                if (!is_array($option)) {
                    $html.= $this->_optionToHtml(array(
                        'value' => $key,
                        'label' => $option),
                        $value
                    );
                }
                elseif (is_array($option['value'])) {
                    $html.='<optgroup label="'.$option['label'].'">'."\n";
                    foreach ($option['value'] as $groupItem) {
                        $html.= $this->_optionToHtml($groupItem, $value);
                    }
                    $html.='</optgroup>'."\n";
                }
                else {
                    $html.= $this->_optionToHtml($option, $value);
                }
            }
        }

        $html.= '</select>'."\n";
        $html.= $this->getAfterElementHtml();
        return $html;
	}

}