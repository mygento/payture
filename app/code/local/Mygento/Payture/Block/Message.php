<?php

/**
 * 
 *
 * @category Mygento
 * @package Mygento_Payture
 * @copyright Copyright © 2015 NKS LLC. (http://www.mygento.ru)
 */
class Mygento_Payture_Block_Message extends Mage_Payment_Block_Form
{

    protected function _construct()
    {
        $this->setTemplate('mygento/payture/message.phtml');
        parent::_construct();
    }
}
