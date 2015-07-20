<?php

/**
 *
 *
 * @category Mygento
 * @package Mygento_Payture
 * @copyright Copyright © 2015 NKS LLC. (http://www.mygento.ru)
 */
class Mygento_Payture_Block_Info extends Mage_Payment_Block_Info
{

    public function getOid()
    {
        $info = $this->getInfo();
        if ($info instanceof Mage_Sales_Model_Order_Payment) {
            $order = $info->getOrder();
            //$id=$order->getData('increment_id');
            //$order=Mage::getSingleton('sales/order')->loadByIncrementId($id);
            return $order->getId();
        }
        return false;
    }

    public function getPaylink()
    {
        return Mage::helper('payture')->getLink($this->getOid());
    }

    public function isPaid()
    {
        $order = Mage::getModel('sales/order')->load($this->getOid());
        if (!$order->hasInvoices()) {
            return false;
        } else {
            return true;
        }
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('mygento/payture/info.phtml');
    }

    public function getOrder()
    {
        $info = $this->getInfo();
        if ($info instanceof Mage_Sales_Model_Order_Payment) {
            return $info->getOrder();
        }
    }

    public function getTotalSum()
    {
        $info = $this->getInfo();
        if ($info instanceof Mage_Sales_Model_Order_Payment) {
            $order = $info->getOrder();
            return round($order->getGrandTotal(), 2);
        }
    }

    public function getRefundlink()
    {
        return Mage::helper("adminhtml")->getUrl("payture/adminhtml_index/refund/", array('_secure' => true, 'order' => $this->getOid()));
    }

    public function getUnblockTransactionlink()
    {
        return Mage::helper("adminhtml")->getUrl("payture/adminhtml_index/cancel/", array('_secure' => true, 'order' => $this->getOid()));
    }

    public function getAcceptTransactionlink()
    {
        return Mage::helper("adminhtml")->getUrl("payture/adminhtml_index/complete/", array('_secure' => true, 'order' => $this->getOid()));
    }

    public function getState()
    {
        $collection = Mage::getModel('payture/keys')->getCollection();
        $collection->addFieldToFilter('orderid', $this->getOid());
        if (count($collection) == 0) {
            return false;
        } else {
            $item = $collection->getFirstItem();
            return $item->getState();
        }
    }

    public function getPaytureName()
    {
        return $this->escapeHtml(Mage::getStoreConfig('payment/payture/title'));
    }
}
