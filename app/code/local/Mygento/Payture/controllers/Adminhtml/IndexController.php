<?php

/**
 * Sea Lab Ltd.
 *
 * @category Mygento
 * @package Mygento_Payture
 * @copyright Copyright © 2014 Sea Lab Ltd. (http://www.mygento.ru)
 */
class Mygento_Payture_Adminhtml_IndexController extends Mage_Adminhtml_Controller_Action {

    public function indexAction() {
        echo 'Nope. Visit <a href="http://www.mygento.ru/">Magento development</a>';
    }

    public function completeAction() {
        $order_inc_id=$this->getRequest()->getParam('order');
        $order=Mage::getModel('sales/order')->load($order_inc_id);
        if ($order->getId()) {
            $req=array(
                'Key'=>Mage::helper('payture')->getKey(),
                'OrderId'=>$order->getId(),
                'Password'=>Mage::helper('payture')->getPassword(),
            );
            $url=Mage::helper('payture')->getHost().'Charge?'.http_build_query($req);
            Mage::helper('payture')->AddLog($url);
            $xml=simplexml_load_file($url);
            Mage::helper('payture')->AddLog($xml);
            if ($xml["Success"] == 'True') {
                $collection=Mage::getModel('payture/keys')->getCollection();
                $collection->addFieldToFilter('orderid',$order->getId());
                $item=$collection->getFirstItem();
                $sess=Mage::getModel('payture/keys')->load($item->getId());
                $sess->setState('Complete');
                $sess->save();
                Mage::helper('payture')->addTransaction($order);
            }
        }
        $url=Mage::helper("adminhtml")->getUrl("adminhtml/sales_order/view",array('_secure'=>true,'order_id'=>$order->getId()));
        Mage::app()->getResponse()->setRedirect($url);
    }

    public function unblockAction() {
        $order_inc_id=$this->getRequest()->getParam('order');
        $order=Mage::getModel('sales/order')->load($order_inc_id);
        $postData=Mage::app()->getRequest()->getPost();
        if ($order->getId() && $postData['sum']) {
            $req=array(
                'Key'=>Mage::helper('payture')->getKey(),
                'OrderId'=>$order->getId(),
                'Password'=>Mage::helper('payture')->getPassword(),
                'Amount'=>round($postData['sum'] * 100,0),
            );
            $url=Mage::helper('payture')->getHost().'Unblock?'.http_build_query($req);
            Mage::helper('payture')->AddLog($url);
            $xml=simplexml_load_file($url);
            Mage::helper('payture')->AddLog($xml);
            if ($xml["Success"] == 'True') {
                $collection=Mage::getModel('payture/keys')->getCollection();
                $collection->addFieldToFilter('orderid',$order->getId());
                $item=$collection->getFirstItem();
                $sess=Mage::getModel('payture/keys')->load($item->getId());
                $sess->setState('Unblocked');
                $sess->save();
                Mage::helper('payture')->addTransaction($order);
            }
        }
        $url=Mage::helper("adminhtml")->getUrl("adminhtml/sales_order/view",array('_secure'=>true,'order_id'=>$order->getId()));
        Mage::app()->getResponse()->setRedirect($url);
    }

    public function refundAction() {
        $order_inc_id=$this->getRequest()->getParam('order');
        $order=Mage::getModel('sales/order')->load($order_inc_id);
        $postData=Mage::app()->getRequest()->getPost();
        if ($order->getId() && $postData['sum']) {
            $req=array(
                'Key'=>Mage::helper('payture')->getKey(),
                'OrderId'=>$order->getId(),
                'Password'=>Mage::helper('payture')->getPassword(),
                'Amount'=>round($postData['sum'] * 100,0),
            );
            $url=Mage::helper('payture')->getHost().'Refund?'.http_build_query($req);
            Mage::helper('payture')->AddLog($url);
            $xml=simplexml_load_file($url);
            Mage::helper('payture')->AddLog($xml);
            if ($xml["Success"] == 'True') {
                $collection=Mage::getModel('payture/keys')->getCollection();
                $collection->addFieldToFilter('orderid',$order->getId());
                $item=$collection->getFirstItem();
                $sess=Mage::getModel('payture/keys')->load($item->getId());
                $sess->setState('Refunded');
                $sess->save();
                Mage::helper('payture')->addTransaction($order);
            }
        }
        $url=Mage::helper("adminhtml")->getUrl("adminhtml/sales_order/view",array('_secure'=>true,'order_id'=>$order->getId()));
        Mage::app()->getResponse()->setRedirect($url);
    }

}
