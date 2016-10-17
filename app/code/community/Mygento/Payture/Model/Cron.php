<?php

/**
 *
 *
 * @category Mygento
 * @package Mygento_Payture
 * @copyright Copyright © 2016 NKS LLC. (http://www.mygento.ru)
 */
class Mygento_Payture_Model_Cron
{

    public function fivemin()
    {
        Mage::helper('payture')->addLog('Start of cron run');
        $collection = Mage::getModel('payture/keys')->getCollection();
        $collection->addFieldToFilter('sessionid', array('neq' => null));
        $collection->addFieldToFilter('state', array('neq' => 'Complete'));
        Mage::helper('payture')->addLog('Checking ' . count($collection) . ' order tickets');
        foreach ($collection as $_ticket) {
            Mage::helper('payture')->checkTicket($_ticket);
        }
        Mage::helper('payture')->addLog('End of cron run');
    }
}
