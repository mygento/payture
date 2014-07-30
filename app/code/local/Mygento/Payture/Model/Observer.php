<?php

/**
 * Sea Lab Ltd.
 *
 * @category Mygento
 * @package Mygento_Payture
 * @copyright Copyright © 2014 Sea Lab Ltd. (http://www.mygento.ru)
 */
class Mygento_Payture_Model_Observer extends Varien_Object {

    public function sendEmail($observer) {
        $order=$observer->getEvent()->getOrder();
        if ($order->getPayment()->getMethodInstance()->getCode() == 'payture') {
            Mage::helper('payture')->sendemailbyorder($order);
        }
    }

}
