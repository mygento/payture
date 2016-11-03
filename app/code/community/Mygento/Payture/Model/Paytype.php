<?php

/**
 *
 *
 * @category Mygento
 * @package Mygento_Payture
 * @copyright Copyright © 2016 NKS LLC. (http://www.mygento.ru)
 */
class Mygento_Payture_Model_Paytype
{

    public function toOptionArray()
    {
        return [
                ['value' => 'Pay', 'label' => Mage::helper('payture')->__('One-step payment')],
                ['value' => 'Block', 'label' => Mage::helper('payture')->__('Two-step payment')]
        ];
    }
}
