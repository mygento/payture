<?php

/**
 *
 *
 * @category Mygento
 * @package Mygento_Payture
 * @copyright Copyright © 2016 NKS LLC. (http://www.mygento.ru)
 */
class Mygento_Payture_Adminhtml_Payture_IndexController extends Mage_Adminhtml_Controller_Action
{

    public function indexAction()
    {
        $this->getResponse()->setBody('Nope. Visit <a href="http://www.mygento.ru/">Magento development</a>');
    }

    public function completeAction()
    {
        $order_inc_id = $this->getRequest()->getParam('order');
        $order = Mage::getModel('sales/order')->load($order_inc_id);
        if ($order->getId()) {
            $req = array(
                'Key' => Mage::helper('payture')->getKey(),
                'OrderId' => $order->getId(),
                'Password' => Mage::helper('payture')->getPassword(),
            );
            $url = Mage::helper('payture')->getHost() . 'Charge?' . http_build_query($req);
            Mage::helper('payture')->addLog($url);
            $xml = Mage::helper('payture')->getData($url);
            Mage::helper('payture')->addLog($xml);
            if ($xml["Success"] == 'True') {
                $collection = Mage::getModel('payture/keys')->getCollection();
                $collection->addFieldToFilter('orderid', $order->getId());
                $item = $collection->getFirstItem();
                $sess = Mage::getModel('payture/keys')->load($item->getId());
                $sess->setState('Complete');
                $sess->save();
                Mage::helper('payture')->addTransaction($order);

                //Check the ticket
                $link = Mage::helper('payture')->getLink($order->getId());
                $ticket = Mage::helper('payture')->decodeid($link);
                Mage::helper('payture')->checkTicket($ticket);
            }
        }
        $url = Mage::helper("adminhtml")->getUrl("adminhtml/sales_order/view", array('_secure' => true, 'order_id' => $order->getId()));
        Mage::app()->getResponse()->setRedirect($url);
    }

    public function unblockAction()
    {
        $order_inc_id = $this->getRequest()->getParam('order');
        $postData = Mage::app()->getRequest()->getPost();

        $redirectLink = $this->processOrder('Unblock', $postData, $order_inc_id);

        //Check the ticket
        $link = Mage::helper('payture')->getLink($order_inc_id);
        $ticket = Mage::helper('payture')->decodeid($link);
        Mage::helper('payture')->checkTicket($ticket);

        Mage::app()->getResponse()->setRedirect($redirectLink);
    }

    public function refundAction()
    {
        $order_inc_id = $this->getRequest()->getParam('order');
        $postData = Mage::app()->getRequest()->getPost();
        Mage::app()->getResponse()->setRedirect($this->processOrder('Refund', $postData, $order_inc_id));
    }

    private function processOrder($type, $postData, $order_inc_id)
    {
        $order = Mage::getModel('sales/order')->load($order_inc_id);
        if ($order->getId() && $postData['sum']) {
            $req = array(
                'Key' => Mage::helper('payture')->getKey(),
                'OrderId' => $order->getId(),
                'Password' => Mage::helper('payture')->getPassword(),
                'Amount' => round($postData['sum'] * 100, 0),
            );
            $url = Mage::helper('payture')->getHost() . $type . '?' . http_build_query($req);
            Mage::helper('payture')->addLog($url);
            $xml = Mage::helper('payture')->getData($url);
            Mage::helper('payture')->addLog($xml);
            if ($xml["Success"] == 'True') {
                $collection = Mage::getModel('payture/keys')->getCollection();
                $collection->addFieldToFilter('orderid', $order->getId());
                $item = $collection->getFirstItem();
                $sess = Mage::getModel('payture/keys')->load($item->getId());
                $sess->setState($type . 'ed');
                $sess->save();
                Mage::helper('payture')->addTransaction($order);
            }
        }
        return Mage::helper("adminhtml")->getUrl("adminhtml/sales_order/view", array('_secure' => true, 'order_id' => $order->getId()));
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('mygento/payture');
    }
}
