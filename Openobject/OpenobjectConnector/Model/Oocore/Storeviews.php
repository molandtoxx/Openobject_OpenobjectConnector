<?php
/**
Openobject Magento Connector
Generic API Extension for Magento Community/Enterprise Editions
This connector is a reboot of the original Openlabs OpenERP Connector
Copyright 2014 Kyle Waid
Copyright 2009 Openlabs / Sharoon Thomas
Some works Copyright by Mohammed NAHHAS
*/

class Openobject_OpenobjectConnector_Model_Oocore_Storeviews extends Mage_Catalog_Model_Api_Resource
{
        public function list($filters=null)
        {
            try
            {
            $collection = Mage::getModel('core/store')->getCollection();//->addAttributeToSelect('*');
            }
            catch (Mage_Core_Exception $e)
            {
               $this->_fault('store_not_exists');
            }
            
            if (is_array($filters)) {
                try {
                    foreach ($filters as $field => $value) {
                        $collection->addFieldToFilter($field, $value);
                    }
                } catch (Mage_Core_Exception $e) {
                    $this->_fault('filters_invalid', $e->getMessage());
                    // If we are adding filter on non-existent attribute
                }
            }

            $result = array();
            foreach ($collection as $customer) {
                $result[] = $customer->toArray();
            }

            return $result;
        }

    public function info($storeIds = null)
    {
        $stores = array();

        if(is_array($storeIds))
        {
            foreach($storeIds as $storeId)
            {
                try
                                {
                                    $stores[] = Mage::getModel('core/store')->load($storeId)->toArray();
                }
                                catch (Mage_Core_Exception $e)
                                {
                                    $this->_fault('store_not_exists');
                                }
                        }
                        return $stores;
        }
                elseif(is_numeric($storeIds))
        {
            try
                        {
                            return Mage::getModel('core/store')->load($storeIds)->toArray();
            }
                        catch (Mage_Core_Exception $e)
                        {
                            $this->_fault('store_not_exists');
                        }

                }
        
        }

        public function create($storedata)
        {
            try
            {
                $store = Mage::getModel('core/store')
                    ->setData($storedata)
                    ->save();

            }
            catch (Magento_Core_Exception $e)
            {
                $this->_fault('data_invalid',$e->getMessage());
            }
            catch (Exception $e)
            {
                $this->_fault('data_invalid',$e->getMessage());
            }
            return $store->getId();
        }

        public function update($storeid,$storedata)
        {
            try
            {
                $store = Mage::getModel('core/store')
                    ->load($storeid);
                if (!$store->getId())
                {
                    $this->_fault('store_not_exists');
                }
                $store->addData($storedata)->save();
            }
            catch (Magento_Core_Exception $e)
            {
                $this->_fault('data_invalid',$e->getMessage());
            }
            catch (Exception $e)
            {
                $this->_fault('data_invalid',$e->getMessage());
            }
            return true;
        }

        public function delete($storeid)
        {
            try
            {
                $store = Mage::getModel('core/store')
                    ->load($storeid);
                if (!$store->getId())
                {
                    $this->_fault('store_not_exists');
                }
                $store->delete();

            }
            catch (Magento_Core_Exception $e)
            {
                $this->_fault('data_invalid',$e->getMessage());
            }
            catch (Exception $e)
            {
                $this->_fault('data_invalid',$e->getMessage());
            }
            return true;
        }
}
?>
