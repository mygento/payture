<?php
/**
 * 
 *
 * @category Mygento
 * @package Mygento_Payture
 * @copyright Copyright © 2016 NKS LLC. (http://www.mygento.ru)
 */
$installer = $this;
$installer->startSetup();

$installer->getConnection()->dropTable('payture/keys');

$payture_table = $installer->getConnection()
    ->newTable($installer->getTable('payture/keys'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
        'nullable' => false,
        'primary' => true,
        'auto_increment' => true,
        ), 'ID')
    ->addColumn('hkey', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable' => false,
        ), 'ID')
    ->addColumn('orderid', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(
        'unsigned' => true,
        'nullable' => false,
        ), 'ID')
    ->addColumn('sessionid', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable' => false,
        ), 'Int Session Id')
    ->addColumn('paytype', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable' => false,
        ), 'Payment type')
    ->addColumn('state', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable' => false,
        ), 'State')
    ->addIndex($installer->getIdxName('payture/keys', array(
        'id'
    )), array(
    'id'
    ), array(
    'type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
    ));

$installer->getConnection()->createTable($payture_table);

$installer->endSetup();
