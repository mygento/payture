<?php

/**
 * 
 *
 * @category Mygento
 * @package Mygento_Payture
 * @copyright Copyright © 2015 NKS LLC. (http://www.mygento.ru)
 */
class Mygento_Payture_Model_Resource_Keys extends Mage_Core_Model_Resource_Db_Abstract {

    public function _construct() {
        $this->_init('payture/keys','id');
    }

}
