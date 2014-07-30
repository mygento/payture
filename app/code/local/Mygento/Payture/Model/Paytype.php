<?php

/**
 * Sea Lab Ltd.
 *
 * @category Mygento
 * @package Mygento_Payture
 * @copyright Copyright © 2014 Sea Lab Ltd. (http://www.mygento.ru)
 */
class Mygento_Payture_Model_Paytype {

    public function toOptionArray() {
        return array(
            array('value'=>'Pay','label'=>Mage::helper('payture')->__('One-step payment')),
            array('value'=>'Block','label'=>Mage::helper('payture')->__('Two-step payment')),
        );
    }

}
