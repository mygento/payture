<?php

/**
 *
 *
 * @category Mygento
 * @package Mygento_Yandexkassa
 * @copyright Copyright © 2016 NKS LLC. (http://www.mygento.ru)
 */
$installer = $this;
$installer->startSetup();

$conn = $installer->getConnection();

$conn->addColumn(
        $installer->getTable('payture/keys'), 'date', array(
    'type' => Varien_Db_Ddl_Table::TYPE_DATETIME,
    'nullable' => true,
    'comment' => 'store ticket date',
        )
);

$installer->endSetup();
