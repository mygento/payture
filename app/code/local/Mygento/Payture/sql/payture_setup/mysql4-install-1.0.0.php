<?php

/**
 * 
 *
 * @category Mygento
 * @package Mygento_Payture
 * @copyright Copyright © 2015 NKS LLC. (http://www.mygento.ru)
 */
$this->startSetup();

$this->run("DROP TABLE IF EXISTS {$this->getTable('payture/keys')};");

$this->run("CREATE TABLE {$this->getTable('payture/keys')} (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hkey` varchar(255) NOT NULL,
  `orderid` int(11) NOT NULL,
  `sessionid` varchar(255) DEFAULT NULL,
  `paytype` varchar(255) NOT NULL,
  `state` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;");

$this->endSetup();
