<?php

/**
 *
 *
 * @category Mygento
 * @package Mygento_Payture
 * @copyright Copyright © 2016 NKS LLC. (http://www.mygento.ru)
 */
class Mygento_Payture_Model_Observer extends Varien_Object
{
    public function sendEmail($observer)
    {
        $order = $observer->getEvent()->getOrder();
        if ($order->getPayment()->getMethodInstance()->getCode() == 'payture') {
            Mage::helper('payture')->sendEmailByOrder($order);
        }
    }

    /*
     * Credit memo
     */
    public function cancelCheque($observer)
    {
        $creditmemo = $observer->getEvent()->getCreditmemo();
        $order      = $creditmemo->getOrder();

        if (!Mage::helper('payture')->isPaidBy($order)) {
            return;
        }

        Mage::helper('payture')->addLog('CREDIT MEMO: ' . $creditmemo->getIncrementId());

        if ($creditmemo->getOrigData() && $creditmemo->getOrigData('increment_id')) {
            return;
        }

        Mage::getModel('payture/payture')->modifyOrder('Refund', $creditmemo, $order);
    }
}
