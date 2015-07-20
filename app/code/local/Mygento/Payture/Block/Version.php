<?php

/**
 *
 *
 * @category Mygento
 * @package Mygento_Payture
 * @copyright Copyright © 2015 NKS LLC. (http://www.mygento.ru)
 */
class Mygento_Payture_Block_Version extends Mage_Adminhtml_Block_Abstract implements Varien_Data_Form_Element_Renderer_Interface
{

    /**
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     *
     * @SuppressWarnings("unused")
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $info = '<fieldset class="config success-msg" style="padding-left:30px;"><a target="_blank" href="http://www.mygento.ru/"><img src="//www.mygento.ru/media/favicon/default/favicon.png" width="16" height="16" />' . $this->__('Magento Development') . '</a>';
        $info.='<a style="float:right" target="_blank" href="https://github.com/mygento/payture">' . $this->__('Module on Github') . '</a></fieldset>';
        return $info;
    }
}
