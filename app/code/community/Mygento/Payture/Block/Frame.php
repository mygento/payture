<?php

/**
 *
 *
 * @category Mygento
 * @package Mygento_Payture
 * @copyright Copyright © 2016 NKS LLC. (http://www.mygento.ru)
 */
class Mygento_Payture_Block_Frame extends Mage_Core_Block_Template
{

    public function __construct()
    {
        $this->setTemplate('mygento/payture/iframe.phtml');
    }
}
