<?php

/**
 *
 *
 * @category Mygento
 * @package Mygento_Payture
 * @copyright 2017 NKS LLC. (https://www.mygento.ru)
 */
class Mygento_Payture_Model_Source_Tax
{

    public function toOptionArray()
    {
        return [
            ['value' => 1, 'label' => Mage::helper('payture')->__('Ставка НДС 18%')],
            ['value' => 2, 'label' => Mage::helper('payture')->__('Ставка НДС 10%')],
            ['value' => 3, 'label' => Mage::helper('payture')->__('Ставка НДС расч. 18/118')],
            ['value' => 4, 'label' => Mage::helper('payture')->__('Ставка НДС расч. 10/110')],
            ['value' => 5, 'label' => Mage::helper('payture')->__('Ставка НДС 0%')],
            ['value' => 6, 'label' => Mage::helper('payture')->__('НДС не облагается')]
        ];
    }
}
