<?php

/**
 *
 *
 * @category Mygento
 * @package Mygento_Payture
 * @copyright Copyright © 2016 NKS LLC. (http://www.mygento.ru)
 */
class Mygento_Payture_Helper_Data extends Mage_Core_Helper_Abstract
{

    public function addLog($text)
    {
        if (Mage::getStoreConfig('payment/payture/debug')) {
            Mage::log($text, null, 'payture.log', true);
        }
    }

    public function getKey()
    {
        return Mage::helper('core')->decrypt(Mage::getStoreConfig('payment/payture/key'));
    }

    public function getPassword()
    {
        return Mage::helper('core')->decrypt(Mage::getStoreConfig('payment/payture/password'));
    }

    public function sendEmailByOrder($order)
    {
        try {
            $order->sendNewOrderEmail();
        } catch (Exception $e) {
            $this->addLog($e->getMessage());
        }
    }

    public function getLink($order_id)
    {
        $collection = Mage::getModel('payture/keys')->getCollection();
        $collection->addFieldToFilter('orderid', $order_id);
        if (count($collection) == 0) {
            $model = Mage::getModel('payture/keys');
            $key = strtr(base64_encode(microtime() . $order_id . rand(1, 1048576)), '+/=', '-_,');
            $model->setHkey($key);
            $model->setOrderid($order_id);
            $model->setSessionid(null);
            $model->setDate(null);
            $model->save();
            return Mage::getUrl('payture/payment/paynow/', array('_secure' => true, 'order' => $key));
        } else {
            $item = $collection->getFirstItem();
            return Mage::getUrl('payture/payment/paynow/', array('_secure' => true, 'order' => $item->getHkey()));
        }
    }

    public function decodeid($link)
    {
        $collection = Mage::getModel('payture/keys')->getCollection();
        $collection->addFieldToFilter('hkey', $link);
        if (count($collection) == 0) {
            return false;
        }
        $item = $collection->getFirstItem();
        return $item;
    }

    public function addtransaction($order)
    {
        $orders = Mage::getModel('sales/order_invoice')->getCollection()
                ->addAttributeToFilter('order_id', array('eq' => $order->getId()));
        $orders->getSelect()->limit(1);
        if ((int) $orders->count() !== 0) {
            return $this;
        }
        if ($order->getState() == Mage_Sales_Model_Order::STATE_NEW) {
            try {
                if (!$order->canInvoice()) {
                    $order->addStatusHistoryComment('Payture_Invoicer: Order cannot be invoiced.', false);
                    $order->save();
                }
                $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
                $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE);
                $invoice->register();
                $invoice->getOrder()->setCustomerNoteNotify(false);
                $invoice->getOrder()->setIsInProcess(true);
                $order->addStatusHistoryComment('Automatically INVOICED by Payture_Invoicer.', false);
                $transactionSave = Mage::getModel('core/resource_transaction')
                        ->addObject($invoice)
                        ->addObject($invoice->getOrder());
                $transactionSave->save();
                if (Mage::getStoreConfig('payment/payture/send')) {
                    $order->sendOrderUpdateEmail($order->getStatus(), Mage::getStoreConfig('payment/payture/text'));
                }
                if (Mage::getStoreConfig('payment/payture/sendinvoice')) {
                    $invoice->sendEmail();
                }
                if (Mage::getStoreConfig('payment/payture/sendadmin') != '') {
                    $this->sendCustomComment($order, Mage::getStoreConfig('payment/payture/sendadmin'), Mage::getStoreConfig('payment/payture/text'));
                }
            } catch (Exception $e) {
                $order->addStatusHistoryComment('Payture_Invoicer: Exception occurred during automaticall transaction action. Exception message: ' . $e->getMessage(), false);
                $order->save();
            }
        }
    }

    private function sendCustomComment($order, $toemail, $comment)
    {
        $storeId = $order->getStore()->getId();

        $mailer = Mage::getModel('core/email_template_mailer');
        $emailInfo = Mage::getModel('core/email_info');
        $emailInfo->addTo($toemail, Mage::getStoreConfig('trans_email/ident_sales/name'));

        if ($order->getCustomerIsGuest()) {
            $templateId = Mage::getStoreConfig(self::XML_PATH_UPDATE_EMAIL_GUEST_TEMPLATE, $storeId);
        } else {
            $templateId = Mage::getStoreConfig(self::XML_PATH_UPDATE_EMAIL_TEMPLATE, $storeId);
        }
        $mailer->addEmailInfo($emailInfo);

        $mailer->setSender(Mage::getStoreConfig(self::XML_PATH_UPDATE_EMAIL_IDENTITY, $storeId));
        $mailer->setStoreId($storeId);
        $mailer->setTemplateId($templateId);
        $mailer->setTemplateParams(array(
            'order' => $order,
            'comment' => $comment,
            'billing' => $order->getBillingAddress()
        ));
        $mailer->send();
    }

    public function checkTicket($_ticket)
    {
        $url = $this->getHost() . 'PayStatus?Key=' . Mage::helper('payture')->getKey() . '&OrderId=' . $_ticket->getOrderid();
        $xml = simplexml_load_string($this->getData($url));
        if ($xml['Success'] == 'True') {
            Mage::getModel('payture/payture')->processStatus($xml["State"], $_ticket->getOrderid(), $_ticket->getId());
        }
    }

    public function getHost()
    {
        if (Mage::getStoreConfig('payment/payture/test')) {
            return 'https://sandbox.payture.com/apim/';
        }
        return 'https://secure.payture.com/apim/';
    }

    public function getData($url)
    {
        // @codingStandardsIgnoreStart
        $ch = curl_init();
        $timeout = 10;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $data = curl_exec($ch);
        curl_close($ch);
        // @codingStandardsIgnoreEnd
        $this->addLog($data);
        return $data;
    }
}
