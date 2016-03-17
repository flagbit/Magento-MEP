<?php

class Flagbit_MEP_Model_Export_Entity_Product extends Mage_ImportExport_Model_Export_Entity_Product
{

    protected $_configurable_delimiter = '|';

    protected $_attributeMapping = null;

    protected $_threads = array();

    protected $_categoryIds = array();

    /**
     * export limit
     *
     * @var null
     */
    protected $_limit = null;

    /**
     * Attribute Models
     * @var array
     */
    protected $_attributeModels = array();

    /**
     * @var Flagbit_MEP_Model_Profile
     */
    protected $_profile = null;

    /**
     * Cache value for parent and children products
     *
     * @var array
     */
    protected $_itemsCache = array('parents' => array(), 'children' => array());

    /**
     * Shipping attribute array
     *
     * @var array
     */
    protected $_shippingAttrCodes;

    /**
     * Tax config object
     *
     * @var array
     */
    protected $_taxConfig = null;

    /**
     * Google Mapping
     *
     * @var array
     */
    protected $_googleMapping = null;

    /**
     * Temporary Stock Items
     *
     * @var array
     */
    protected $_stockItems = array();

    /**
     * Configuration for product suffix url
     * @var null|string
     */
    protected $_seoSuffixUrl = null;

    /**
     * Constructor.
     *
     * @return void
     */
    public function __construct($options = array())
    {
        $this->setParameters($options);
        parent::__construct();
    }

    /**
     * Initialize categories ID to text-path hash.
     *
     * @return Mage_ImportExport_Model_Export_Entity_Product
     */
    protected function _initCategories()
    {
        $collection = Mage::getResourceModel('catalog/category_collection')->addNameToResult();
        /* @var $collection Mage_Catalog_Model_Resource_Eav_Mysql4_Category_Collection */
        foreach ($collection as $category) {
            $structure = preg_split('#/+#', $category->getPath());
            $pathSize = count($structure);
            if ($pathSize > 1) {
                $path = array();
                $pathIds = array();
                for ($i = 1; $i < $pathSize; $i++) {
                    if(is_a($collection->getItemById($structure[$i]),'Mage_Catalog_Model_Category')){
                        $path[] = $collection->getItemById($structure[$i])->getName();
                        $pathIds[] = $structure[$i];
                    }
                }
                $this->_rootCategories[$category->getId()] = array_shift($path);
                if ($pathSize > 2) {
                    $this->_categories[$category->getId()] = implode($this->getProfile()->getCategoryDelimiter(), $path);
                    $this->_categoryIds[$category->getId()] = $pathIds;
                }
            }

        }
        return $this;
    }

    /**
     * init Writer
     *
     * @param $writer Mage_ImportExport_Model_Export_Adapter_Abstract
     */
    protected function  _initWriter(&$writer)
    {
        $obj_profile = $this->getProfile();

        $settings = $obj_profile->getSettings();
        $encoding = null;
        if (!empty($settings['encoding'])) {
            $encoding = $settings['encoding'];
        }

        $writer->setDelimiter($obj_profile->getDelimiter());
        $writer->setConfigurableDelimiter($this->_configurable_delimiter);
        $writer->setEnclosure($obj_profile->getEnclose());
        $writer->setEncoding($encoding);

        // add Twig Templates
        $writer->setTwigTemplate($obj_profile->getTwigHeaderTemplate(), 'header');
        $writer->setTwigTemplate($obj_profile->getTwigContentTemplate(), 'content');
        $writer->setTwigTemplate($obj_profile->getTwigFooterTemplate(), 'footer');

        if ($obj_profile->getOriginalrow() == 1) {
            $writer->setHeaderRow(true);
        } else {
            $writer->setHeaderRow(false);
        }
    }

    /**
     * get Attribute Mapping
     *
     * @param bool $attributeCode
     * @return array|bool|null
     */
    protected function _getAttributeMapping($attributeCode = false)
    {
        if ($this->_attributeMapping === null) {
            /* @var $attributeMappingCollection Flagbit_MEP_Model_Mysql4_Attribute_Mapping_Collection */
            $attributeMappingCollection = Mage::getResourceModel('mep/attribute_mapping_collection')->load();
            $this->_attributeMapping = array();
            foreach ($attributeMappingCollection as $attributeMapping) {
                $this->_attributeMapping[$attributeMapping->getAttributeCode()] = $attributeMapping;
            }
        }
        if ($attributeCode !== false) {
            if (isset($this->_attributeMapping[$attributeCode])) {
                return $this->_attributeMapping[$attributeCode];
            } else {
                return false;
            }
        }
        return $this->_attributeMapping;
    }

    protected function  _getAttributeShipping($attributeCode) {
        if (array_key_exists($attributeCode, $this->_shippingAttrCodes)) {
            return $this->_shippingAttrCodes[$attributeCode];
        }
        return null;
    }

    /**
     * set export Limit
     *
     * @param $limit
     * @return $this
     */
    public function setLimit($limit)
    {
        $this->_limit = $limit;
        return $this;
    }

    /**
     * Get current quantity of products for the export profile
     *
     * @return int
     */
    public function countItems() {

        if ($this->hasProfileId()) {
            $filteredProductIds = $this->_getFilteredProductIds();
            if (!empty($filteredProductIds)) {
                $collection = $this->_prepareMepEntityCollection(Mage::getResourceModel('catalog/product_collection'), $filteredProductIds);
                return $collection->getSize();
            }
        }
        return 0;
    }

    /**
     * Run ids of products, filtered by MEP rules
     *
     * @return array of Ids
     */
    protected function _getFilteredProductIds() {
        /* @var $obj_profile Flagbit_MEP_Model_Profile */
        $obj_profile = $this->getProfile();

        Mage::helper('mep/log')->debug('START Filter Rules', $this);

        // LOAD FILTER RULES
        /* @var $ruleObject Flagbit_MEP_Model_Rule */
        $ruleObject = Mage::getModel('mep/rule');
        $rule = $obj_profile->getConditionsSerialized();
        if (!is_array($rule)) {
            $rule = unserialize($rule);
        }
        $ruleObject->setProfile($obj_profile);
        $ruleObject->loadPost(array('conditions' => $rule));
        $ruleObject->setWebsiteIds(array(Mage::app()->getStore($obj_profile->getStoreId())->getWebsiteId()));
        Mage::helper('mep/log')->debug('Get matching product', $this);
        if ($this->_limit) {
            $ruleObject->setLimit($this->_limit);
        }
        $filteredProductIds = $ruleObject->getMatchingProductIds();
        Mage::helper('mep/log')->debug('END Filter Rules', $this);

        return $filteredProductIds;
    }

    /**
     * Apply filter to collection and add not skipped attributes to select.
     *
     * Supports adding of additional parameter $filteredProductIds that allows to extend filtering
     * base Magento filtering, provided by parent::_prepareEntityCollection() method.
     *
     * @param Mage_Eav_Model_Entity_Collection_Abstract $collection
     * @param array $filteredIds
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    protected function _prepareMepEntityCollection(Mage_Eav_Model_Entity_Collection_Abstract $collection, $filteredIds) {

        /* @var $obj_profile Flagbit_MEP_Model_Profile */
        $obj_profile = $this->getProfile();

        /* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
        $collection = parent::_prepareEntityCollection($collection);
        $collection->setStoreId(0)->addStoreFilter($obj_profile->getStoreId());

        $collection->addFieldToFilter("entity_id", array('in' => $filteredIds));

        return $collection;
    }

    /**
     * Export process.
     *
     * @return string
     */
    public function export()
    {
        //Execution time may be very long
        set_time_limit(0);

        $this->_initTaxConfig();

        $this->_initGoogleMapping();

        Mage::app()->setCurrentStore(0);

        Mage::app()->loadArea(Mage_Core_Model_App_Area::AREA_ADMINHTML);


        /** @var $collection Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection */
        $validAttrCodes = array();
        $shippingAttrCodes = array();
        $writer = $this->getWriter();


        if ($this->hasProfileId()) {
            Mage::helper('mep/log')->info('Starting export '.$this->getProfileId(), $this);

            /* @var $obj_profile Flagbit_MEP_Model_Profile */
            $obj_profile = $this->getProfile();
            //Mage::app()->setCurrentStore($obj_profile->getStoreId());

            $this->_seoSuffixUrl = (string) Mage::app()->getStore($obj_profile->getStoreId())->getConfig(
                Mage_Catalog_Helper_Product::XML_PATH_PRODUCT_URL_SUFFIX
            );

            $this->_configurable_delimiter = $obj_profile->getConfigurableValueDelimiter();

            $this->_storeIdToCode[0] = 'admin';
            $this->_storeIdToCode[$obj_profile->getStoreId()] = Mage::app()->getStore($obj_profile->getStoreId())->getCode();

            $this->_initWriter($writer);

            // Get Shipping Mapping
            $shipping_id = $obj_profile->getShippingId();
            if (!empty($shipping_id)) {
                $collection = Mage::getModel('mep/shipping_attribute')->getCollection();
                $collection->addFieldToFilter('profile_id', array('eq' => $shipping_id));
                foreach ($collection as $item) {
                    $shippingAttrCodes[$item->getAttributeCode()] = $item;
                }
            }

            // get Field Mapping
            /* @var $mapping Flagbit_MEP_Model_Mysql4_Mapping_Collection */
            $mapping = Mage::getModel('mep/mapping')->getCollection();
            $mapping->addFieldToFilter('profile_id', array('eq' => $this->getProfileId()));
            $mapping->setOrder('position', 'ASC');
            $mapping->load();

            foreach ($mapping->getItems() as $item) {
                $validAttrCodes[] = $item->getToField();
            }

            $filteredProductIds = $this->_getFilteredProductIds();
            if (count($filteredProductIds) < 1) {
                Mage::helper('mep/log')->warn('Nothing to export ' . $this->getProfileId(), $this);
                return 'No data';
            }
            $collection = $this->_prepareMepEntityCollection(Mage::getResourceModel('catalog/product_collection'), $filteredProductIds);

            $size = $collection->getSize();

            // write quantity to the log
            Mage::helper('mep/log')->debug('EXPORT '.$size.' Products', $this);
            // write quantity to the database
            $obj_profile->getResource()->saveField('product_count', $size, $this->getProfileId());

            // run just a small export for the preview function
            if($this->_limit){
                $this->_exportThread(1, $writer, $this->_limit, $filteredProductIds, $mapping, $shippingAttrCodes);
                return $writer->getContents();
            }

            // to export process in threads for better performance
            $index = 0;
            if($obj_profile->getUseSingleProcess()){
                // sacrifice performance to avoid connection locks, for example
                // on Redis session management
                $limitProducts = $size;
                $maxThreads = 1;
            } else {
                // to export process in threads for better performance
                $limitProducts = 1000;
                $maxThreads = 5;
            }
            while(true){
                $index++;
                $this->_threads[$index] = new Flagbit_MEP_Model_Thread( array($this, '_exportThread') );
                $this->_threads[$index]->start($index, null, $limitProducts, $filteredProductIds, $mapping, $shippingAttrCodes);

                // let the first fork go to ensure that the headline is correct set
                if($index == 1){
                    while($this->_threads[$index]->isAlive()){
                        sleep(1);
                    }
                }

                while( count($this->_threads) >= $maxThreads ) {
                    $this->_cleanUpThreads();
                }
                $this->_cleanUpThreads();

                // export is complete
                if($index >= $size/$limitProducts){
                    break;
                }
            }
            // wait for all the threads to finish
            while( !empty( $this->_threads ) ) {
                $this->_cleanUpThreads();
            }
            $obj_profile->uploadToFtp();

            Mage::helper('mep/log')->info('EXPORT done', $this);
        }

        /**
         * IMPORTANT TO PREVENT MySql to go away
         */
        $core_read = Mage::getSingleton('core/resource')->getConnection('core_read');
        /** @var Varien_Db_Adapter_Pdo_Mysql $core_read */
        $core_read->closeConnection();
        $core_read->getConnection();
    }

    /**
     * clean up finished threads
     */
    protected function _cleanUpThreads()
    {
        foreach( $this->_threads as $index => $thread ) {
            if( ! $thread->isAlive() ) {
                $fileName = Mage::getConfig()->getOptions()->getBaseDir() . DS . $this->getProfile()->getFilepath() . DS . $this->getProfile()->getFilename();
                $newFileName = $fileName . '.new';
                $threadContent = file_get_contents($fileName . '.' . $index . '.tmp');
                file_put_contents($newFileName, $threadContent, FILE_APPEND);
                unlink($fileName . '.' . $index . '.tmp');
                unset( $this->_threads[$index] );
            }
        }
        // let the CPU do its work
        sleep( 1 );
    }

    /**
     * clean up runtime details
     */
    protected function _cleanUpProcess()
    {
        Mage::reset();
        Mage::app('admin', 'store');

        $entityCode = $this->getEntityTypeCode();
        $this->_entityTypeId = Mage::getSingleton('eav/config')->getEntityType($entityCode)->getEntityTypeId();
        $this->_connection   = Mage::getSingleton('core/resource')->getConnection('write');
    }

    /**
     * Main export function, call all needed function to manage inheritance and special attribute
     *
     * @param $offsetProducts
     * @param $writer
     * @param $limitProducts
     * @param $filteredProductIds
     * @param $mapping
     * @param $shippingAttrCodes
     * @return bool
     */
    public function _exportThread($offsetProducts, $writer, $limitProducts, $filteredProductIds, $mapping, $shippingAttrCodes)
    {
        if (is_null($writer))
        {
            $destinationFile = Mage::getConfig()->getOptions()->getBaseDir() . DS . $this->getProfile()->getFilepath() . DS . $this->getProfile()->getFilename() . '.' . $offsetProducts . '.tmp';
            $writer = Mage::helper('mep')->getNewWriteInstance($destinationFile, 'twig');
            $this->_initWriter($writer);
        }
        /**
         * IMPORTANT TO PREVENT MySql to go away
         */
        $core_read = Mage::getSingleton('core/resource')->getConnection('core_read');
        /** @var Varien_Db_Adapter_Pdo_Mysql $core_read */
        $core_read->closeConnection();
        $core_read->getConnection();

        $this->_shippingAttrCodes = $shippingAttrCodes;
        Mage::helper('mep/log')->debug('START Thread: ' . $offsetProducts, $this);
        $objProfile = $this->getProfile();
        if($this->_limit !== null &&  $offsetProducts > 1){
            return false;
        }
        $storeId = $objProfile->getStoreId();
        Mage::helper('mep/log')->debug('Setting store id: ' . $storeId, $this);
        $resource = Mage::getResourceModel('catalog/product_collection');
        $collection = $this->_prepareEntityCollection($resource);
        $collection
            ->setStoreId($storeId)
            ->addStoreFilter($objProfile->getStoreId())
            ->setPage($offsetProducts, $limitProducts)
            ->addAttributeToSelect('*');
        if (!empty($filteredProductIds)){
            $collection->addFieldToFilter("entity_id", array('in' => $filteredProductIds));
        }

        Mage::helper('mep/log')->debug('Looping on products', $this);

        if($objProfile->getUseSingleProcess()) {
            // we need to walk collection one by one, slower but needed.
            // due to race conditions on Redis we are invoking only one thread
            // with all products loaded in collection.
            Mage::getSingleton('core/resource_iterator')->walk(
                $collection->getSelect(),
                array(array($this, '_writeRow')),
                array('mapping' => $mapping, 'offset' => $offsetProducts, 'writer' => $writer)
            );
        } else {
            // iterate collection as usual since each thread only will load 1000 SKUs
            foreach ($collection as $item) {
                $this->_writeRow(array(
                        'row' => $item, 'mapping' => $mapping, 'offset' => $offsetProducts, 'writer' => $writer
                    ));
            }

            $collection->clear();
        }

        Mage::helper('mep/log')->debug('END Thread: ' . $offsetProducts, $this);
        if ($collection->getCurPage() < $offsetProducts) {
            return false;
        }
        return true;
    }

    /**
     * This work as a callback and as utility function for getting the row and write it.
     *
     * @param array $args
     */
    public function _writeRow($args) {

        if(is_array($args['row'])) {
            $item = Mage::getModel('catalog/product');
            $item->setData($args['row']);
        } else {
            $item = $args['row'];
        }

        $mapping = $args['mapping'];
        $offsetProducts = $args['offset'];
        $writer = $args['writer'];

        $currentRow = array();
        foreach ($mapping->getItems() as $mapItem) {
            $attrValues = array();
            $attrInheritance = $mapItem->getInheritance();
            foreach ($mapItem->getAttributeCodeAsArray() as $attrCode) {
                if ($attrInheritance == 1) {
                    $attrValues = $this->_manageAttributeInheritance($item, $attrCode, $mapItem);
                }
                else {
                    $currentValue = $this->_manageAttributeForItem($item, $attrCode, $mapItem);
                    $this->_addAttributeToArray($currentValue, $attrValues);
                }

                //Taking care of existing value for current attribute
                $newAttrCode = $attrCode;
                $attrCount = 1;
                while (array_key_exists($newAttrCode, $currentRow))
                {
                    $newAttrCode = $attrCode . '_' . $attrCount++;
                }
                $attrCode = $newAttrCode;

                $currentRow[$attrCode] = implode($this->_configurable_delimiter, $attrValues);
            }
        }
        if($offsetProducts != 1) {
            $writer->setHeaderIsDisabled();
        }
        try {
            $writer->writeRow($currentRow);
        }
        catch (Exception $e) {
            Mage::helper('mep/log')->err('Twig exception: ' . $e->getMessage(), $this);
        }

    }

    /*
     * Check if a product has inherited product, get attribute value if so and cache them
     * Get attribute value from normal item if no inherited product
     */
    protected function  _manageAttributeInheritance($item, $attrCode, $mapItem)
    {
        $attrValues = array();
        $inheritanceType = $mapItem->getInheritanceType();
        if ($inheritanceType == 'from_child') {
            $cacheKey = 'children';
        }
        elseif ($inheritanceType == 'from_parent') {
            $cacheKey = 'parents';
        }
        else {
            return null;
        }
        $hasInheritor = false;
        if (!isset($this->_itemsCache[$cacheKey][$item->getId()])) { //If there are no inheritor cached for the current item
            if ($inheritanceType == 'from_child') {
                $inheritorIds = $item->getTypeInstance()->getChildrenIds($item->getId(), false);
                if (isset($inheritorIds[0])) {
                    $inheritorIds = $inheritorIds[0];
                }
            }
            else {
                $inheritorIds = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($item->getId());
                if (empty($inheritorIds)) {
                    $inheritorIds = Mage::getModel('catalog/product_type_grouped')->getParentIdsByChild($item->getId());
                }
            }
            $this->_itemsCache[$cacheKey][$item->getId()] = array();
            if (!empty($inheritorIds)) { //If there are inheritors
                $hasInheritor = true;
                $attrValues = $this->_doInheritanceAndCache($item, $inheritorIds, $attrCode, $mapItem, $cacheKey);
            }
        }
        else { //If there are inheritor cached
            $inheritor = $this->_itemsCache[$cacheKey][$item->getId()];
            if (!empty($inheritor)) { //If there are inheritor
                $hasInheritor = true;
                $attrValues = $this->_doInheritance($inheritor, $attrCode, $mapItem);
            }
        }
        if (!$hasInheritor) {
            $currentValue = $this->_manageAttributeForItem($item, $attrCode, $mapItem); //If there are no inheritor, we use the normal item to get attribute value
            $this->_addAttributeToArray($currentValue, $attrValues);
        }
        return $attrValues;
    }

    /*
     * Parse each inherited product to get attribute value
     */
    protected function  _doInheritance($items, $attrCode, $mapItem) {
        $attrValues = array();
        foreach ($items as $item) {
            $currentValue = $this->_manageAttributeForItem($item, $attrCode, $mapItem);
            $this->_addAttributeToArray($currentValue, $attrValues);
        }
        return $attrValues;
    }

    /**
     * @param Mage_Catalog_Model_Product $parent
     * @param array $items
     * @param string $attrCode
     * @param Flagbit_MEP_Model_Mapping $mapItem
     * @param string $cacheType
     * @return array
     *
     * Parse each inherited product to get attribute value and cache them
     */
    protected function  _doInheritanceAndCache($parent, $items, $attrCode, $mapItem, $cacheType){
        $attrValues = array();
        /** @var Mage_Catalog_Model_Resource_Product_Collection $collection */
        $collection = Mage::helper('mep')->getProductsCollection();
        $collection->addAttributeToSelect('*');
        $settings = $this->getProfile()->getSettings();
        if (!empty($settings['is_in_stock']) && $settings['is_in_stock'] == 2) {
            $settings['is_in_stock'] = '';
        }
        if (isset($settings['is_in_stock']) && strlen($settings['is_in_stock']))
        {
            $isInStockFilter = intval($settings['is_in_stock']);
            $isInStockCondition = 'is_in_stock = ' . $isInStockFilter;
            if ($isInStockFilter == 1)
            {
                $isInStockCondition = '(' . $isInStockCondition . ' OR manage_stock = 0)';
            }
            $collection->getSelect()->where($isInStockCondition);
        }
        if (!empty($settings['qty'])) {
            if (isset($settings['qty']['threshold']) && strlen($settings['qty']['threshold'])) {
                $operator = Mage::helper('mep/qtyFilter')->getOperatorForSqlFilter($settings['qty']['operator']);
                $threshold = $settings['qty']['threshold'];
                $collection->getSelect()->where('qty ' . $operator . ' ?', $threshold);
            }
        }
        $collection->addFieldToFilter("entity_id", array('in' => $items));
        // ensure we don't get duplicates products
        $collection->groupByAttribute('sku');
        $items = $collection->load();

        foreach ($items as $item) {
            /** @var Mage_Catalog_Model_Product $item */
            $itemId = $item->getId();
            $currentValue = $this->_manageAttributeForItem($item, $attrCode, $mapItem);
            $this->_addAttributeToArray($currentValue, $attrValues);
            $this->_itemsCache[$cacheType][$parent->getId()][$itemId] = $item; //Add the item to the cache
        }
        return $attrValues;
    }

    /*
     * Insert a new attribute value in the given array if the value is not empty and not already in the array
     */
    protected function  _addAttributeToArray($value, &$attrValues) {
        if (strlen($value) && !in_array($value, $attrValues)) {
            $attrValues[] = $value;
        }
    }

    /*
     * Manage attribute value for a given item
     */
    protected function  _manageAttributeForItem($item, $attrCode, $mapItem) {
        //Mage::app()->setCurrentStore($this->getProfile()->getStoreId());
        if (($attributeMapping = $this->_getAttributeMapping($attrCode))) {
            $attrValue = $this->_manageAttributeMapping($attributeMapping, $item);
        }
        elseif (($attributeShipping = $this->_getAttributeShipping($attrCode))) {
            $attrValue = Mage::helper('mep/shipping')->emulateCheckout($item, $this->getProfile()->getStoreId(), $attributeShipping);
        }
        else {
            $attrValue = $this->_getAttributeValue($item, $attrCode, $mapItem);
        }
        //Mage::app()->setCurrentStore(0);
        return $attrValue;
    }

    /*
     * Get attribute value for a given item
     * Apply filters if necessary
     */
    protected function  _getAttributeValue($item, $attrCode, $mapItem)
    {
        //Callback method configuration for special attribute
        Mage::app()->setCurrentStore($this->getProfile()->getStoreId());
        $attributeValueFilter = array(
            'price_catalog_rule' => '_getPriceWithCatalogRule',
            'url' => '_getProductUrl',
            'price' => '_getPrice',
            'gross_price' => '_getGrossPrice',
            'qty' => '_getQuantity',
            'is_in_stock' => '_getIsInStock',
            'image_url' => '_getImageUrl',
            '_category' => '_getProductCategory',
            '_category_id' => '_getProductCategoryId',
            '_categories' => '_getProductCategories',
            '_media_gallery' => '_getMediaGallery',
            'base_price_reference_amount' => '_getBasePriceReferenceAmount',
            'is_salable' => '_getIsSalable',
            'google_mapping' => '_getGoogleMapping',
            'manage_stock' => '_getManageStock',
			'type' => '_getType'
        );
        $attrValue = $item->getData($attrCode);

        if(empty($attrValue)) {
            $item->load($item->getId());
            $attrValue = $item->getData($attrCode);
        }

        if (isset($attributeValueFilter[$attrCode])) {
            $attrValue = $this->$attributeValueFilter[$attrCode]($item, $mapItem);
        }
        if (isset($this->_attributeValues[$attrCode])) {
            if (isset($this->_attributeValues[$attrCode][$attrValue])) {
                $attrValue = $this->_attributeValues[$attrCode][$attrValue];
            }
        }
        if (isset($this->_attributeTypes[$attrCode])) {
            if ($this->_attributeTypes[$attrCode] == 'multiselect') {
                $currentValues = explode(',', $attrValue);
                foreach ($currentValues as &$currentValue) {
                    if (isset($this->_attributeValues[$attrCode][$currentValue])) {
                        $currentValue = $this->_attributeValues[$attrCode][$currentValue];
                    }
                }
                $attrValue = implode(',', $currentValues);
            }
        }
        Mage::app()->setCurrentStore(0);

        $eventObject = new Varien_Object(array('value' => $attrValue, 'attribute_code' => $attrCode, 'item' => $mapItem));
        Mage::dispatchEvent('mep_product_attribute_value', array('export' => $eventObject));

        return $eventObject->getValue();
    }

    /*
     * Map attribute value
     */
    protected function  _manageAttributeMapping($attributeMapping, $item) {
        $sourceAttributeCode = $attributeMapping->getSourceAttributeCode();
        $attrValue = $item->getData($sourceAttributeCode);
        if ($sourceAttributeCode == 'category') {
            $itemCategoriesIds = $item->getCategoryIds();
            $categoryId = array_shift($itemCategoriesIds);
            if (empty($categoryId)) {
                return null;
            }
            $currentCount = 0;
            foreach ($itemCategoriesIds as $itemCategoryId) {
                if (isset($this->_categoryIds[$itemCategoryId]) && count($this->_categoryIds[$itemCategoryId]) > $currentCount) {
                    $categoryId = $itemCategoryId;
                }
                else {
                    break;
                }
                $currentCount = count($this->_categoryIds[$itemCategoryId]);
            }
            if ($attributeMapping->getCategoryType() == 'single') {
                if (isset($this->_categoryIds[$categoryId])) {
                    $attrValue = implode($this->getProfile()->getCategoryDelimiter(), $attributeMapping->getOptionValue($this->_categoryIds[$categoryId], $this->getProfile()->getStoreId()));
                    return $attrValue;
                }
            }
            else {
                $attrValue = $attributeMapping->getOptionValue($categoryId, $this->getProfile()->getStoreId());
                return $attrValue;
            }
        }
        else {
            if (!empty($attrValue)) {
                if ($this->_attributeTypes[$sourceAttributeCode] == 'multiselect') {
                    $attrValue = $attributeMapping->getOptionValue(explode(',', $attrValue), $this->getProfile()->getStoreId());
                    $attrValue = implode(',', $attrValue);

                } else {
                    $attrValue = $attributeMapping->getOptionValue($attrValue, $this->getProfile()->getStoreId());
                }
                return $attrValue;
            }
        }
        return null;
    }

    protected function _getProductUrl($item, $mapItem)
    {
        $attrValue = $item->getProductUrl(false);

        return $attrValue;
    }

    /**
     * Add the suffix if needed
     * @param $url
     * @param $seoSuffix
     * @return string
     */
    protected function _addSuffixToUrl($url, $seoSuffix)
    {
        if (!preg_match('%\.[^/]+$%', $url) && !empty($seoSuffix)) {
            $url .= '.' . $seoSuffix;
        }

        return $url;
    }

    protected function _getPrice($item, $mapItem)
    {
        $objProfile = $this->getProfile();

        if ($item->getTypeId() == 'bundle') {
            $includeTax = null;

            $displayConfig = $this->_taxConfig->getPriceDisplayType($objProfile->getStoreId());
            if($displayConfig == Mage_Tax_Model_Config::DISPLAY_TYPE_BOTH) {
                $includeTax = true;
            }

            return Mage::getModel('bundle/product_price')->getTotalPrices($item, 'min', $includeTax);
        }

        return $item->getPrice();
    }

    protected function  _getPriceWithCatalogRule($item, $mapItem)
    {
        $objProfile = $this->getProfile();

        $item->setWebsiteId($item->getStore()->getWebsiteId());
        $item->setCustomerGroupId(0);

        $finalPrice = $item->getFinalPrice();

        return $finalPrice;
    }

    /**
     * Get product price with including taxes
     *
     * @param Mage_Catalog_Model_Product $item
     * @param $mapItem
     *
     * @return float
     */
    protected function  _getGrossPrice($item, $mapItem)
    {
        if ($item->getTypeId() == 'bundle') {
            return Mage::getModel('bundle/product_price')->getTotalPrices($item, 'min', true);
        } elseif ($item->getTypeId() == 'grouped') {
            // find the product with the lowest price
            $children = $item->getTypeInstance(true)->getAssociatedProducts($item);
            if (count($children)) {
                $price = INF;
                foreach ($children as $child) {
                    if ($price > $child->getPrice()) {
                        $price = $child->getPrice();
                        //use the child instead of parent to get the proper price
                        $item = $child;
                    }
                }
            }
        }

        $objProfile = $this->getProfile();

        try {
            $price = Mage::helper('tax')->getPrice(
                $item, $item->getPrice(), true, null, null, null, $objProfile->getStoreId()
            );
        } catch (Mage_Core_Exception $e) {
            $price = $item->getPrice();
        }

        return $price;
    }

    protected function _getQuantity($item, $mapItem)
    {
        if ($item->getTypeId() == 'grouped') {
            $children = $item->getTypeInstance(true)->getAssociatedProducts($item);
            if (count($children)) {
                // find first child product with a non-zero qty
                foreach ($children as $child) {
                    $stockItem = $this->_getStockItem($child);
                    if ($stockItem->getQty() > 0) {
                        return intval($stockItem->getQty());
                    }
                }
            }
        }

        $stockItem = $this->_getStockItem($item);

        return intval($stockItem->getQty());
    }

    protected function _getIsInStock($item, $mapItem)
    {
        $status = $this->_getStockItem($item);
        return intval($status->getIsInStock());
    }

    protected function  _getImageUrl($item, $mapItem) {
        $item->load('media_gallery');
        $options = unserialize($mapItem->getOptions());
        $image_type = (empty($options['image_url_type']) ? 'image' : $options['image_url_type']);
        if ($item->getData($image_type) == 'no_selection') {
            $image_type = 'image';
        }
        $attrValue = $item->getMediaConfig()->getMediaUrl($item->getData($image_type));
        return $attrValue;
    }

    /**
     * @param $item Mage_Catalog_Model_Product
     * @param $mapItem
     */
    protected function _getMediaGallery($item, $mapItem) {
        $item->load('media_gallery');
        $images = array();
        foreach($item->getMediaGalleryImages() as $image) {
            $images[] = $image['url'];
        }

        return implode($this->getProfile()->getMediaGalleryDelimiter(), $images);
    }

    protected function  _getProductCategory($item, $mapItem) {
        $categoryIds = $item->getCategoryIds();
        $categoryId = null;
        $max = 0;
        foreach ($categoryIds as $_categoryId) {
            if(isset($this->_categoryIds[$_categoryId]) && count($this->_categoryIds[$_categoryId]) > $max){
                $max = count($this->_categoryIds[$_categoryId]);
                $categoryId = $_categoryId;
            }
        }
        $attrValue = '';
        if (isset($this->_categories[$categoryId])) {
            $attrValue = $this->_categories[$categoryId];
        }
        return $attrValue;
    }

    protected function  _getProductCategoryId($item, $mapItem) {
        $categoryIds = $item->getCategoryIds();
        $categoryId = null;
        $max = 0;
        foreach ($categoryIds as $_categoryId) {
            if(isset($this->_categoryIds[$_categoryId]) && count($this->_categoryIds[$_categoryId]) > $max){
                $max = count($this->_categoryIds[$_categoryId]);
                $categoryId = $_categoryId;
            }
        }
        $attrValue = '';
        if (isset($this->_categoryIds[$categoryId])) {
            $attrValue = implode($this->getProfile()->getCategoryDelimiter(), $this->_categoryIds[$categoryId]);
        }
        return $attrValue;
    }

    protected function  _getProductCategories($item, $mapItem) {
        $categoryIds = $item->getCategoryIds();
        $attrValue = '';
        $categories = array();
        foreach ($categoryIds as $_categoryId) {
            if(isset($this->_categoryIds[$_categoryId])){
                if (isset($this->_categories[$_categoryId])) {
                    $categories[] = $this->_categories[$_categoryId];
                }
            }
        }
        if (count($categories) > 0) {
            $attrValue = implode($this->getProfile()->getConfigurableValueDelimiter(), $categories);
        }
        return $attrValue;
    }

    protected function _getBasePriceReferenceAmount($item, $mapItem) {
        $attrValue = Mage::helper('baseprice')->getBasePriceLabel($item, '{{baseprice}}');
        $attrValue = str_replace(array(' €'), '', strip_tags($attrValue));
        return $attrValue;
    }

    protected function  _getIsSalable($item, $mapItem) {
        $attrValue = intval($item->getTypeInstance()->isSalable());
        return $attrValue;
    }

    protected function _getManageStock($item, $mapItem)
    {
        $status = $this->_getStockItem($item);
        return intval($status->getManageStock());
    }

    protected function  _getGoogleMapping($item, $mapItem) {
        $categoryIds = $item->getCategoryIds();
        $categoryId = null;
        $max = 0;
        foreach ($categoryIds as $_categoryId) {
            if(isset($this->_categoryIds[$_categoryId]) && count($this->_categoryIds[$_categoryId]) > $max){
                $max = count($this->_categoryIds[$_categoryId]);
                $categoryId = $_categoryId;
            }
        }
        $attrValue = '';
        if (isset($this->_categoryIds[$categoryId])) {
            $options = unserialize($mapItem->getOptions());
            $mappingType = $options['google_mapping_type'];
            $mappingSeparator = $options['google_mapping_separator'];
            if (empty($mappingType)) {
                return $attrValue;
            }
            $categories = $this->_categoryIds[$categoryId];
            $mapped = array();
            foreach ($categories as $category) {
                if (!empty($this->_googleMapping[$category])) {
                    if ($mappingType == 'last') {
                        $element = array_slice($this->_googleMapping[$category], -1);
                        $mapped[] = $element[0];
                    }
                    elseif ($mappingType == 'complete') {
                        $mapped[] = implode($this->getProfile()->getCategoryDelimiter(), $this->_googleMapping[$category]);
                    }
                }
            }
            if (empty($mappingSeparator)) {
                $mappingSeparator = ',';
            }
            $attrValue = implode($mappingSeparator, $mapped);
        }
        return $attrValue;
    }

    protected function _getType($item, $mapItem)
    {
        return $item->getTypeId();
    }

    /**
     * Initialize attribute option values and types.
     *
     * @return Mage_ImportExport_Model_Export_Entity_Product
     */
    protected function _initAttributes()
    {
        foreach ($this->getAttributeCollection() as $attribute) {
            $this->_attributeValues[$attribute->getAttributeCode()] = $this->getAttributeOptions($attribute);
            $this->_attributeTypes[$attribute->getAttributeCode()] =
                Mage_ImportExport_Model_Import::getAttributeType($attribute);
            $this->_attributeModels[$attribute->getAttributeCode()] = $attribute;
        }
        return $this;
    }

    /**
     * Init tax config
     *
     * @return Mage_Tax_Model_Config
     */
    protected function _initTaxConfig()
    {
        if(is_null($this->_taxConfig)) {
            $this->_taxConfig = Mage::getSingleton('tax/config');
        }
        return $this->_taxConfig;
    }

    protected function _getStockItem(Mage_Catalog_Model_Product $product)
    {
        if(!isset($this->_stockItems[$product->getId()])) {
            $stockInfos = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product->getId());
            $this->_stockItems[$product->getId()] = $stockInfos;
        }

        return $this->_stockItems[$product->getId()];
        //return $product->getData('stock_item');
    }

    /**
     * Init google mapping
     *
     * @return array
     */
    protected function  _initGoogleMapping() {
        $model = Mage::getModel('mep/googleMapping')->getCollection();
        foreach ($model as $mapping) {
            $mappingIds = explode('|', $mapping->getGoogleMappingIds());
            $currentMapping = array();
            foreach ($mappingIds as $mappingId) {
                $current = Mage::getModel('mep/googleTaxonomies')->load($mappingId);
                $currentMapping[] = $current->getName();
            }
            $this->_googleMapping[$mapping->getCategoryId()] = $currentMapping;
        }
    }

    /**
     * @return Flagbit_MEP_Model_Profile|Mage_Core_Model_Abstract|null
     */
    public function getProfile()
    {
        if ($this->_profile == null && $this->hasProfileId()) {
            $this->_profile = Mage::getModel('mep/profile')->load($this->getProfileId());
        }
        return $this->_profile;
    }

    /**
     * @return bool
     */
    public function hasProfileId()
    {
        return array_key_exists('id', $this->_parameters);
    }

    /**
     * @return int
     */
    public function getProfileId()
    {
        return (int)$this->_parameters['id'];
    }

    /**
     * Returns attributes all values in label-value or value-value pairs form. Labels are lower-cased.
     *
     * @param Mage_Eav_Model_Entity_Attribute_Abstract $attribute
     * @return array
     */
    public function getAttributeOptions(Mage_Eav_Model_Entity_Attribute_Abstract $attribute)
    {
        $options = array();

        if ($attribute->usesSource()) {
            // should attribute has index (option value) instead of a label?
            $index = in_array($attribute->getAttributeCode(), $this->_indexValueAttributes) ? 'value' : 'label';

            /* MEP changed admin store to current profile store id */
            $attribute->setStoreId(
                $this->getProfile()->getStoreId()
            );

            try {
                foreach ($attribute->getSource()->getAllOptions(false) as $option) {
                    foreach (is_array($option['value']) ? $option['value'] : array($option) as $innerOption) {
                        if (strlen($innerOption['value'])) { // skip ' -- Please Select -- ' option
                            $options[$innerOption['value']] = $innerOption[$index];
                        }
                    }
                }
            } catch (Exception $e) {
                // ignore exceptions connected with source models
            }
        }
        return $options;
    }

}
