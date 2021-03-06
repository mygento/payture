<?php

/**
 *
 *
 * @category Mygento
 * @package Mygento_Payture
 * @copyright 2017 NKS LLC. (https://www.mygento.ru)
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
            $key   = strtr(base64_encode(microtime() . $order_id . rand(1, 1048576)), '+/=', '-_,');
            $model->setHkey($key);
            $model->setOrderid($order_id);
            $model->setSessionid(null);
            $model->setDate(null);
            $model->save();
            return Mage::getUrl('payture/payment/paynow/', array('_secure' => true, 'order' => $key));
        } else {
            $item = $collection->getFirstItem();
            return Mage::getUrl(
                'payture/payment/paynow/',
                array('_secure' => true, 'order' => $item->getHkey())
            );
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
                    $order->addStatusHistoryComment(
                        'Payture_Invoicer: Order cannot be invoiced.',
                        false
                    );
                    $order->save();
                }
                $invoice         = Mage::getModel('sales/service_order', $order)->prepareInvoice();
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
                    $order->sendOrderUpdateEmail(
                        $order->getStatus(),
                        Mage::getStoreConfig('payment/payture/text')
                    );
                }
                if (Mage::getStoreConfig('payment/payture/sendinvoice')) {
                    $invoice->sendEmail();
                }
                if (Mage::getStoreConfig('payment/payture/sendadmin') != '') {
                    $this->sendCustomComment(
                        $order,
                        Mage::getStoreConfig('payment/payture/sendadmin'),
                        Mage::getStoreConfig('payment/payture/text')
                    );
                }
            } catch (Exception $e) {
                $order->addStatusHistoryComment(
                    'Payture_Invoicer: Exception occurred during automaticall transaction action. Exception message: ' . $e->getMessage(),
                    false
                );
                $order->save();
            }
        }
    }

    private function sendCustomComment($order, $toemail, $comment)
    {
        $storeId = $order->getStore()->getId();

        $mailer    = Mage::getModel('core/email_template_mailer');
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
            'order'   => $order,
            'comment' => $comment,
            'billing' => $order->getBillingAddress()
        ));
        $mailer->send();
    }

    public function checkTicket($_ticket)
    {
        $url = $this->getHost() . 'PayStatus?Key=' . $this->getKey() . '&OrderId=' . $_ticket->getOrderid();
        $xml = simplexml_load_string($this->getData($url));
        if ($xml['Success'] == 'True') {
            Mage::getModel('payture/payture')->processStatus(
                $xml["State"],
                $_ticket->getOrderid(),
                $_ticket->getId()
            );
        }
    }

    public function getHost()
    {
        if (Mage::getStoreConfig('payment/payture/test')) {
            return 'https://sandbox3.payture.com/apim/';
        }
        return 'https://secure.payture.com/apim/';
    }

    public function getData($url)
    {
        // @codingStandardsIgnoreStart
        $ch      = curl_init();
        $timeout = 10;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $data    = curl_exec($ch);
        curl_close($ch);
        // @codingStandardsIgnoreEnd
        $this->addLog($data);
        return $data;
    }

    /**
     *
     * @param type string
     * @return mixed
     */
    public function getConfig($param)
    {
        return Mage::getStoreConfig('payment/payture/' . $param);
    }

    /**
     *
     * @param $entity Mage_Sales_Model_Order | Mage_Sales_Model_Order_Invoice | Mage_Sales_Model_Order_Creditmemo
     * @return type
     */
    public function getOrderItemsJson($entity)
    {
        $shippingTax   = Mage::getStoreConfig('payment/payture/shipping_tax');
        $taxValue      = Mage::getStoreConfig('payment/payture/tax_options');
        $attributeCode = '';

        if (!Mage::getStoreConfig('payment/payture/tax_all')) {
            $attributeCode = Mage::getStoreConfig('payment/payture/product_tax_attr');
        }

        if (!Mage::getStoreConfig('payment/payture/default_shipping_name')) {
            $entity->setShippingDescription(Mage::getStoreConfig('payment/payture/custom_shipping_name'));
        }

        $data   = Mage::helper('payture/discount')->getRecalculated(
            $entity,
            $taxValue,
            $attributeCode,
            $shippingTax
        );
        $result = [];
        foreach ($data['items'] as $item) {
            $result['Positions'][] = [
                'Quantity' => $item['quantity'],
                'Price'    => $item['price'],
                'Tax'      => $item['tax'],
                'Text'     => $item['name'],
            ];
        }

        $result['CustomerContact'] = $entity->getCustomerEmail();

        return Mage::helper('core')->jsonEncode($result);
    }

    public function isPaidBy($order)
    {
        if (strpos($order->getPayment()->getMethod(), 'payture') !== false) {
            return true;
        }
        return false;
    }

    /** Performs a request to Payture and processes incoming data (save to order and payture/key entity)
     *
     */
    public function processTransaction($type, $req, $order)
    {
        // @codingStandardsIgnoreStart
        $this->addLog('Cheque ' . $type . ' base64_json_decode: ' . print_r(Mage::helper('core')->jsonDecode(base64_decode($req['Cheque'])),1));
        // @codingStandardsIgnoreEnd
        $url = $this->getHost() . $type . '?' . http_build_query($req);
        $this->addLog($url);
        $xml = $this->getData($url);
        $this->addLog($xml);
        if ($xml["Success"] == 'True') {
            $collection = Mage::getModel('payture/keys')->getCollection();
            $collection->addFieldToFilter('orderid', $order->getId());
            $item = $collection->getFirstItem();
            $sess = Mage::getModel('payture/keys')->load($item->getId());
            $sess->setState($type . 'ed');
            $sess->save();
            $this->addTransaction($order);
        }
        return $xml;
    }
}
