<?php

/**
*Openobject Magento Connector
*Generic API Extension for Magento Community/Enterprise Editions
*This connector is a reboot of the original Openlabs OpenERP Connector
*Copyright 2014 Kyle Waid
*Copyright 2009 Openlabs / Sharoon Thomas
*Some works Copyright by Mohammed NAHHAS
*/

class Openobject_OpenobjectConnector_Model_Oocatalog_Product_Attribute extends Mage_Catalog_Model_Api_Resource {
    public function __construct() {
        $this->_storeIdSessionField = 'product_store_id';
        $this->_ignoredAttributeCodes[] = 'type_id';
        $this->_ignoredAttributeTypes[] = 'gallery';
        $this->_ignoredAttributeTypes[] = 'media_image';
    }

    /**
     * Retrieve attributes Option Info
     *
     * @param int $setId
     * @return array
     */

    public function optioninfo($optionId, $store = null) {
        /* I do not understand the purpose of this function and it may not be needed */
        $coreResource = Mage::getSingleton('core/resource');
        $conn = $coreResource->getConnection('core_read');
        if (!$store) {
            $store = 0;
        }
        $select = $conn->select()
            ->from($coreResource->getTableName('eav_attribute_option_value'), array('eav_attribute_option_value.option_id',
                                                                                    'option.attribute_id',
                                                                                    'value',
                                                                                    'value_id',
                                                                                    'store_id'
                                                                                    ))
            ->joinLeft(array("option" => 'eav_attribute_option'), "eav_attribute_option_value.option_id = option.option_id")
            ->where('eav_attribute_option_value.option_id = ?', $optionId)
            ->where('store_id = ?', $store);
        $result = $conn->fetchRow($select);
        if ($result) {
            return $result;
        }

        return false;
    }


    public function relations($setId) {
        /* Return all attributes from a given set */
        $attributes = Mage :: getModel('catalog/product')->getResource()->loadAllAttributes()->getSortedAttributes($setId);
        $result = array ();
        foreach ($attributes as $attribute){
            $result[] = Array(
			'group_id' => $attribute->getData('attribute_set_info/' . $setId . '/group_id'),
                        'attribute_id' => $attribute->getId()
                            );
        }
        return $result;

    }


    public function items($setId) {
        /* Return a list of all attributes given a set */
        $attributes = Mage::getResourceModel('catalog/product_attribute_collection');
        $attributes->addFieldToFilter('attribute_code', array('nin' => array('sku',
                                                                    'name',
                                                                    'description',
                                                                    'short_description',
                                                                    'weight',
                                                                    'category_ids',
                                                                    'set',
                                                                    'price',
                                                                    'cost',
                                                                    'visibility',
                                                                    'custom_design'
                                                                    )));
        $result = array ();

        foreach ($attributes as $attribute) {
            /* @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */

                $result[]=$attribute->toArray();
            }

        return $result;
    }


    public function info($attributeId) {
        /* Get details on an attribute */
        try {
            $attribute = Mage :: getModel('catalog/product')->getResource()->getAttribute($attributeId);
            return $attribute->toArray();
        } 

        catch (Exception $e) {
            $this->_fault('not_exists');
        }
    }


    public function options($attributeId, $store = null) {
        /*Get options/values of an attribute */
        $storeId = $this->_getStoreId($store);
        $attribute = Mage :: getModel('catalog/product')->setStoreId($storeId)->getResource()->getAttribute($attributeId)->setStoreId($storeId);

        /* @var $attribute Mage_Catalog_Model_Entity_Attribute */
        if (!$attribute) {
            $this->_fault('not_exists');
        }

        $options = array ();
            foreach ($attribute->getSource()->getAllOptions() as $optionId => $optionValue) {
                if (is_array($optionValue)) {
                    if (!$optionValue['label'] || !$optionValue['value']) {
                       	continue;
                    }
		    $optionValue['attribute_id'] = $attributeId;
                    $options[] = $optionValue;
                }
                else {
		    if (!$optionId || !$optionValue) {
			continue;
		    }
                    $options[] = array (
			'attribute_id' => $attributeId,
                        'value' => $optionId,
                        'label' => $optionValue
                    );
                }
        }
        return $options;
    }
}
?>
