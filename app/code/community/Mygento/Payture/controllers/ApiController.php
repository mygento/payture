<?php

/**
 *
 *
 * @category Mygento
 * @package Mygento_Payture
 * @copyright Copyright © 2016 NKS LLC. (http://www.mygento.ru)
 */
class Mygento_Payture_ApiController extends Mage_Core_Controller_Front_Action
{

    public function indexAction()
    {
        $this->getResponse()->setBody('Nope. Visit <a href="http://www.mygento.ru/">Magento development</a>');
    }

    public function frameAction()
    {
        if ($this->getRequest()->isPost()) {
            $postData = Mage::app()->getRequest()->getPost();
            if (!Mage::getModel('payture/payture')->checkSign($postData['orderid'], $postData['hash'])) {
                $this->response->statusCode(401);
                return $this->response;
            }
            $order_id = $postData['orderid'];
            $order = Mage::getModel('sales/order')->load($order_id);
            if (!$order || !$order->getId()) {
                return false;
            }
            if (!$order->canInvoice()) {
                return false;
            }
            $code = $order->getPayment()->getMethodInstance()->getCode();
            if (!$code == 'payture') {
                return false;
            }
            $url2go = Mage::helper('payture')->getLink($order->getId());
            Mage::register('payture_frame_link', $url2go);
        } else {
            $this->response->statusCode(500);
            return $this->response;
        }
    }
}
