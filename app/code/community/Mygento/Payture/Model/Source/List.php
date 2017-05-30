<?php

/**
 *
 *
 * @category Mygento
 * @package Mygento_Payture
 * @copyright 2017 NKS LLC. (https://www.mygento.ru)
 */
class Mygento_Payture_Model_Source_List
{

    public function getAllOptions()
    {
        $attributes = Mage::getModel('eav/config')->getEntityType(Mage_Catalog_Model_Product::ENTITY)->getAttributeCollection();

        $attributes->addFieldToFilter('main_table.frontend_input', ['neq' => 'hidden']);
        $attributes->addFieldToFilter('main_table.frontend_input', ['neq' => 'multiselect']);
        $attributes->addFieldToFilter('main_table.frontend_input', ['neq' => 'boolean']);
        $attributes->addFieldToFilter('main_table.frontend_input', ['neq' => 'date']);
        $attributes->addFieldToFilter('main_table.frontend_input', ['neq' => 'image']);
        $attributes->addFieldToFilter('main_table.frontend_input', ['neq' => 'price']);
        $attributes->addFieldToFilter('used_in_product_listing', '1');

        $attributes->setOrder('frontend_label', 'ASC');

        $_options = [];

        $_options[] = [
            'label' => Mage::helper('payture')->__('No usage'),
            'value' => 0
        ];

        foreach ($attributes as $attr) {
            $label = $attr->getStoreLabel() ? $attr->getStoreLabel() : $attr->getFrontendLabel();
            if ('' != $label) {
                $_options[] = ['label' => $label, 'value' => $attr->getAttributeCode()];
            }
        }
        return $_options;
    }

    public function toOptionArray()
    {
        return $this->getAllOptions();
    }
}
