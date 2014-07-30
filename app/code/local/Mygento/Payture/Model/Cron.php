<?php

/**
 * Sea Lab Ltd.
 *
 * @category Mygento
 * @package Mygento_Payture
 * @copyright Copyright © 2014 Sea Lab Ltd. (http://www.mygento.ru)
 */
class Mygento_Payture_Model_Cron {

    public function fivemin() {
        Mage::helper('payture')->AddLog('Start of cron run');
        $collection=Mage::getModel('payture/keys')->getCollection();
        $collection->addFieldToFilter('sessionid',array('neq'=>NULL));
        foreach ($collection as $_ticket) {
            Mage::helper('payture')->checkTicket($_ticket);
        }
        Mage::helper('payture')->AddLog('End of cron run');
    }

}
