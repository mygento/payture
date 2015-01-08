<?php

/**
 * 
 *
 * @category Mygento
 * @package Mygento_Payture
 * @copyright Copyright © 2015 NKS LLC. (http://www.mygento.ru)
 */
class Mygento_Payture_PaymentController extends Mage_Core_Controller_Front_Action {

    protected $_order;

    public function indexAction() {
        echo 'Nope. Visit <a href="http://www.mygento.ru/">Magento development</a>';
    }

    public function processAction() {
        if (Mage::getStoreConfig('payment/payture/redirect')) {
            $result=array();
            $session=Mage::getSingleton('checkout/session');
            $order=Mage::getModel('sales/order')->loadByIncrementId($session->getLastRealOrderId()); //получаем id заказа
            if (!$order->getId()) {
                return $result;
            }
            $url2go=Mage::helper('payture')->getLink($order->getId());
            //перенаправление на оплату
            $this->_redirectUrl($url2go);
            Mage::helper('payture')->AddLog('Redirecting to immidiate payment');
        } else {
            Mage::helper('payture')->AddLog('NO Redirect');
            $this->_redirect('checkout/onepage/success',array('_secure'=>true));
        }
    }

    public function paynowAction() {
        $session=Mage::getSingleton('checkout/session');
        //сессия
        $enc_key=$this->getRequest()->getParam('order');
        $ticket=Mage::helper('payture')->decodeid($enc_key);
        if ($ticket) {
            $order_id=$ticket->getOrderid();
            if ($order_id) {
                $order=Mage::getModel('sales/order')->load($order_id);
                if ($order->canInvoice()) {
                    $code=$order->getPayment()->getMethodInstance()->getCode();
                    if ($code == 'payture') {
                        $url2go=Mage::getModel('payture/payture')->processOrder($order,$enc_key);
                        if ($url2go) {
                            $this->_redirectUrl($url2go);
                            return;
                        } else {
                            $session->addError(Mage::helper('payture')->__('Error in your order processing'));
                            $this->_redirect('checkout/cart'); //отправка на корзину
                            return;
                        }
                    } else {
                        return;
                    }
                } else {
                    Mage::helper('payture')->AddLog('Order #'.$order_id.' is already paid');
                    $session->addError(Mage::helper('payture')->__('Payment failed. Please try again later.'));
                    $this->_redirect('checkout/cart'); //отправка на корзину
                    return;
                }
            } else {
                $session->addError(Mage::helper('payture')->__('Error. Order not found.'));
                $this->_redirect('checkout/cart'); //отправка на корзину
                return;
            }
        } else {
            $session->addError(Mage::helper('payture')->__('Error. Order not found.'));
            $this->_redirect('checkout/cart'); //отправка на корзину
            return;
        }
    }

    public function resultAction() {
        $enc_key=$this->getRequest()->getParam('order');
        $ticket=Mage::helper('payture')->decodeid($enc_key);
        if ($ticket) {
            Mage::helper('payture')->checkTicket($ticket);
        }
        $session=Mage::getSingleton('checkout/session');
        $session->addSuccess(Mage::helper('payture')->__('You order will be checked soon.'));
        $this->_redirect('checkout/onepage/success',array('_secure'=>true));
    }

}
