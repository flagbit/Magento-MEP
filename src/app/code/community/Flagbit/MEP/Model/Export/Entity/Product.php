<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);
// Mage_ImportExport_Model_Export_Entity_Product
class Flagbit_MEP_Model_Export_Entity_Product extends Mage_ImportExport_Model_Export_Entity_Product
{

    const CONFIG_KEY_PRODUCT_TYPES = 'global/importexport/export_product_types';

    /**
     * Value that means all entities (e.g. websites, groups etc.)
     */
    const VALUE_ALL = 'all';

    /**
     * Permanent column names.
     *
     * Names that begins with underscore is not an attribute. This name convention is for
     * to avoid interference with same attribute name.
     */
    const COL_STORE = '_store';
    const COL_ATTR_SET = '_attribute_set';
    const COL_TYPE = '_type';
    const COL_CATEGORY = '_category';
    const COL_ROOT_CATEGORY = '_root_category';
    const COL_SKU = 'sku';

    protected $_configurable_delimiter = '|';

    /**
     * Pairs of attribute set ID-to-name.
     *
     * @var array
     */
    protected $_attrSetIdToName = array();

    protected $_attributeMapping = null;

    protected $_threads = array();

    /**
     * Categories ID to text-path hash.
     *
     * @var array
     */
    protected $_categories = array();

    protected $_categoryIds = array();

    /**
     * export limit
     *
     * @var null
     */
    protected $_limit = null;

    /**
     * Root category names for each category
     *
     * @var array
     */
    protected $_rootCategories = array();

    /**
     * Attributes with index (not label) value.
     *
     * @var array
     */
    protected $_indexValueAttributes = array(
        'status',
        'tax_class_id',
        'visibility',
        'enable_googlecheckout',
        'gift_message_available',
        'custom_design'
    );

    /**
     * Permanent entity columns.
     *
     * @var array
     */
    protected $_permanentAttributes = array(self::COL_SKU);

    /**
     * Array of supported product types as keys with appropriate model object as value.
     *
     * @var array
     */
    protected $_productTypeModels = array();

    /**
     * Array of pairs store ID to its code.
     *
     * @var array
     */
    protected $_storeIdToCode = array();

    /**
     * Website ID-to-code.
     *
     * @var array
     */
    protected $_websiteIdToCode = array();

    /**
     * Attribute types
     * @var array
     */
    protected $_attributeTypes = array();

    /**
     * Attribute Models
     * @var array
     */
    protected $_attributeModels = array();

    /**
     * @var Flagbit_MEP_Model_Profil
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
     * Constructor.
     *
     * @return void
     */
    public function __construct()
    {
        $entityCode = 'catalog_product';
        $this->_entityTypeId = Mage::getSingleton('eav/config')->getEntityType($entityCode)->getEntityTypeId();
        $this->_connection = Mage::getSingleton('core/resource')->getConnection('write');
    }

    /**
     * Initialize attribute sets code-to-id pairs.
     *
     * @return Mage_ImportExport_Model_Export_Entity_Product
     */
    protected function _initAttributeSets()
    {
        $productTypeId = Mage::getModel('catalog/product')->getResource()->getTypeId();
        foreach (Mage::getResourceModel('eav/entity_attribute_set_collection')
                     ->setEntityTypeFilter($productTypeId) as $attributeSet) {
            $this->_attrSetIdToName[$attributeSet->getId()] = $attributeSet->getAttributeSetName();
        }
        return $this;
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
     * Initialize product type models.
     *
     * @throws Exception
     * @return Mage_ImportExport_Model_Export_Entity_Product
     */
    protected function _initTypeModels()
    {
        $config = Mage::getConfig()->getNode(self::CONFIG_KEY_PRODUCT_TYPES)->asCanonicalArray();
        foreach ($config as $type => $typeModel) {
            if (!($model = Mage::getModel($typeModel, array($this, $type)))) {
                Mage::throwException("Entity type model '{$typeModel}' is not found");
            }
            if (!$model instanceof Mage_ImportExport_Model_Export_Entity_Product_Type_Abstract) {
                Mage::throwException(
                    Mage::helper('importexport')->__('Entity type model must be an instance of Mage_ImportExport_Model_Export_Entity_Product_Type_Abstract')
                );
            }
            if ($model->isSuitable()) {
                $this->_productTypeModels[$type] = $model;
                $this->_disabledAttrs = array_merge($this->_disabledAttrs, $model->getDisabledAttrs());
                $this->_indexValueAttributes = array_merge(
                    $this->_indexValueAttributes, $model->getIndexValueAttributes()
                );
            }
        }
        if (!$this->_productTypeModels) {
            Mage::throwException(Mage::helper('importexport')->__('There are no product types available for export'));
        }
        $this->_disabledAttrs = array_unique($this->_disabledAttrs);

        return $this;
    }

    /**
     * Initialize website values.
     *
     * @return Mage_ImportExport_Model_Export_Entity_Product
     */
    protected function _initWebsites()
    {
        /** @var $website Mage_Core_Model_Website */
        foreach (Mage::app()->getWebsites() as $website) {
            $this->_websiteIdToCode[$website->getId()] = $website->getCode();
        }
        return $this;
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
     * Export process.
     *
     * @return string
     */
    public function export()
    {

        $this->_initTypeModels()
            ->_initAttributes()
            ->_initAttributeSets()
            ->_initWebsites()
            ->_initCategories();

        //Execution time may be very long
        set_time_limit(0);

        Mage::app()->setCurrentStore(0);


        /** @var $collection Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection */
        $validAttrCodes = array();
        $shippingAttrCodes = array();
        $writer = $this->getWriter();


        if ($this->hasProfileId()) {
            /* @var $obj_profile Flagbit_MEP_Model_Profil */
            $obj_profile = $this->getProfile();
            $delimiter = $obj_profile->getDelimiter();
            $settings = $obj_profile->getSettings();
            $encoding = null;
            if (!empty($settings['encoding'])) {
                $encoding = $settings['encoding'];
            }
            $this->_configurable_delimiter = $obj_profile->getConfigurableValueDelimiter();
            $enclosure = $obj_profile->getEnclose();

            $this->_storeIdToCode[0] = 'admin';
            $this->_storeIdToCode[$obj_profile->getStoreId()] = Mage::app()->getStore($obj_profile->getStoreId())->getCode();


            $writer->setDelimiter($delimiter);
            $writer->setConfigurableDelimiter($this->_configurable_delimiter);
            $writer->setEnclosure($enclosure);
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

            $offsetProducts = 0;

            Mage::helper('mep/log')->debug('START Filter Rules', $this);
            // LOAD FILTER RULES
            /* @var $ruleObject Flagbit_MEP_Model_Rule */
            $ruleObject = Mage::getModel('mep/rule');
            $rule = unserialize($obj_profile->getConditionsSerialized());
            $filteredProductIds = array();
            if (!empty($rule) && count($rule) > 1) {
                $ruleObject->setProfile($obj_profile);
                $ruleObject->loadPost(array('conditions' => $rule));
                $ruleObject->setWebsiteIds(array(Mage::app()->getStore($obj_profile->getStoreId())->getWebsiteId()));
                $filteredProductIds = $ruleObject->getMatchingProductIds();
                if(count($filteredProductIds) < 1){
                    return 'No datas';
                }
            }
            Mage::helper('mep/log')->debug('END Filter Rules', $this);

            /* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
            $collection = $this->_prepareEntityCollection(Mage::getResourceModel('catalog/product_collection'));
            $collection->setStoreId(0)->addStoreFilter($obj_profile->getStoreId());

            if(!empty($filteredProductIds)){
                $collection->addFieldToFilter("entity_id", array('in' => $filteredProductIds));
            }

            $size = $collection->getSize();

            Mage::helper('mep/log')->debug('EXPORT '.$size.' Products', $this);

            // run just a small export for the preview function
            if($this->_limit){
                $this->_exportThread(1, $writer, $this->_limit, $filteredProductIds, $mapping, $shippingAttrCodes);
                return $writer->getContents();
            }

            // to export process in threads for better performance
            $index = 0;
            $limitProducts = 1000;
            $maxThreads = 5;
            while(true){
                $index++;
                $this->_threads[$index] = new Flagbit_MEP_Model_Thread( array($this, '_exportThread') );
                $this->_threads[$index]->start($index, $writer, $limitProducts, $filteredProductIds, $mapping, $shippingAttrCodes);

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
            $obj_profile->uploadToFtp();
        }

        // wait for all the threads to finish
        while( !empty( $this->_threads ) ) {
            $this->_cleanUpThreads();
        }
    }

    /**
     * clean up finished threads
     */
    protected function _cleanUpThreads()
    {
        foreach( $this->_threads as $index => $thread ) {
            if( ! $thread->isAlive() ) {
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
        $this->_shippingAttrCodes = $shippingAttrCodes;
        $this->_cleanUpProcess();
        Mage::helper('mep/log')->debug('START Thread: ' . $offsetProducts, $this);
        $objProfile = $this->getProfile();
        if($this->_limit !== null &&  $offsetProducts > 1){
            return false;
        }
        $storeId = $objProfile->getStoreId();
        Mage::app()->setCurrentStore($storeId);

        $collection = $this->_prepareEntityCollection(Mage::getResourceModel('catalog/product_collection'));
        $collection
            ->setStoreId($storeId)
            ->addStoreFilter($objProfile->getStoreId())
            ->setPage($offsetProducts, $limitProducts)
            ->addAttributeToSelect('*');
        if (!empty($filteredProductIds)){
            $collection->addFieldToFilter("entity_id", array('in' => $filteredProductIds));
        }
        $collection->load();
        foreach ($collection as $item) {
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
                echo 'TWIG Exception: ' . $e->getMessage();
            }
        }
        $collection->clear();
        if ($collection->getCurPage() < $offsetProducts) {
            return false;
        }
        return true;
    }

    /*
     * Check if a product has inherited product, get attribute value if so and cache them
     * Get attribute value from normal item if no inherited product
     */

    protected function  _manageAttributeInheritance($item, $attrCode, $mapItem) {
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

    /*
     * Parse each inherited product to get attribute value and cache them
     */
    protected function  _doInheritanceAndCache($parent, $items, $attrCode, $mapItem, $cacheType){
        $attrValues = array();
        $collection = Mage::getResourceModel('catalog/product_collection');
        $collection->addAttributeToSelect('*');
        $settings = $this->getProfile()->getSettings();
        if (!empty($settings['is_in_stock']) && $settings['is_in_stock'] == 2) {
            $settings['is_in_stock'] = '';
        }
        if (isset($settings['is_in_stock']) && strlen($settings['is_in_stock'])) {
            $collection->joinField(
                'is_in_stock',
                'cataloginventory/stock_item',
                'is_in_stock',
                'product_id=entity_id',
                '{{table}}.stock_id=1',
                'left'
            )->addAttributeToFilter('is_in_stock', array('eq' => $settings['is_in_stock']));
        }
        if (!empty($settings['qty'])) {
            if (isset($settings['qty']['threshold']) && strlen($settings['qty']['threshold'])) {
                $operator = $settings['qty']['operator'];
                $threshold = $settings['qty']['threshold'];
                $collection->joinField(
                    'qty',
                    'cataloginventory/stock_item',
                    'qty',
                    'product_id=entity_id',
                    '{{table}}.stock_id=1',
                    'left'
                )->addAttributeToFilter('qty', array(Mage::helper('mep/qtyFilter')->getOperatorForCollectionFilter($operator) => $threshold));
            }
        }
        $collection->addFieldToFilter("entity_id", array('in' => $items));
        $items = $collection->load();
        foreach ($items as $item) {
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
        Mage::app()->setCurrentStore($this->getProfile()->getStoreId());
        if (($attributeMapping = $this->_getAttributeMapping($attrCode))) {
            $attrValue = $this->_manageAttributeMapping($attributeMapping, $item);
        }
        elseif (($attributeShipping = $this->_getAttributeShipping($attrCode))) {
            $attrValue = Mage::helper('mep/shipping')->emulateCheckout($item, $this->getProfile()->getStoreId(), $attributeShipping);
        }
        else {
            $attrValue = $this->_getAttributeValue($item, $attrCode, $mapItem);
        }
        Mage::app()->setCurrentStore(0);
        return $attrValue;
    }

    /*
     * Get attribute value for a given item
     * Apply filters if necessary
     */
    protected function  _getAttributeValue($item, $attrCode, $mapItem) {
        //Callback method configuration for special attribute
        $attributeValueFilter = array(
            'url' => '_getProductUrl',
            'gross_price' => '_getGrossPrice',
            'qty' => '_getQuantity',
            'image_url' => '_getImageUrl',
            '_category' => '_getProductCategory',
            'base_price_reference_amount' => '_getBasePriceReferenceAmount'
        );
        $attrValue = $item->getData($attrCode);
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
        return $attrValue;
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

    protected function  _getProductUrl($item, $mapItem)
    {
        $objProfile = $this->getProfile();
        if (version_compare(Mage::getVersion(), '1.13.0.0') >= 0) {
            $urlRewrite = Mage::getModel('enterprise_urlrewrite/url_rewrite')->getCollection()->addFieldToFilter('target_path', array('eq' => 'catalog/product/view/id/' . $item->getId()))->addFieldToFilter('is_system', array('eq' => 1));
            $attrValue = Mage::app()->getStore($objProfile->getStoreId())->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . $urlRewrite->getFirstItem()->getRequestPath();
        }
        else {
            $attrValue = $item->getProductUrl(false);
        }

        return $attrValue;
    }

    protected function  _getGrossPrice($item, $mapItem) {
        $objProfile = $this->getProfile();
        $attrValue = Mage::helper('tax')->getPrice($item, $item->getFinalPrice(), null, null, null, null, $objProfile->getStoreId(), null);
        return $attrValue;
    }

    protected function  _getQuantity($item, $mapItem) {
        $attrValue = intval(Mage::getModel('cataloginventory/stock_item')->loadByProduct($item)->getQty());
        return $attrValue;
    }

    protected function  _getImageUrl($item, $mapItem) {
        $item->load('media_gallery');
        $attrValue = $item->getMediaConfig()->getMediaUrl($item->getData('image'));
        return $attrValue;
    }

    protected function  _getProductCategory($item, $mapItem) {
        $categoryIds = $item->getCategoryIds();
        $categoryId = null;
        $max = 0;
        foreach ($categoryIds as $_categoryId) {
            if(isset($this->_categoryIds[$_categoryId]) && count($this->_categoryIds[$_categoryId]) > $max){
                $max = $this->_categoryIds[$_categoryId];
                $categoryId = $_categoryId;
            }
        }
        $attrValue = '';
        if (isset($this->_categories[$categoryId])) {
            $attrValue = $this->_categories[$categoryId];
        }
        return $attrValue;
    }

    protected function _getBasePriceReferenceAmount($item, $mapItem) {
        $attrValue = Mage::helper('baseprice')->getBasePriceLabel($item, '{{baseprice}}');
		$attrValue = str_replace(array(' €'), '', strip_tags($attrValue));
        return $attrValue;
    }


    /**
     * Clean up already loaded attribute collection.
     *
     * @param Mage_Eav_Model_Resource_Entity_Attribute_Collection $collection
     * @return Mage_Eav_Model_Resource_Entity_Attribute_Collection
     */
    public function filterAttributeCollection(Mage_Eav_Model_Resource_Entity_Attribute_Collection $collection)
    {
        $validTypes = array_keys($this->_productTypeModels);

        foreach (parent::filterAttributeCollection($collection) as $attribute) {
            $attrApplyTo = $attribute->getApplyTo();
            $attrApplyTo = $attrApplyTo ? array_intersect($attrApplyTo, $validTypes) : $validTypes;

            if ($attrApplyTo) {
                foreach ($attrApplyTo as $productType) { // override attributes by its product type model
                    if ($this->_productTypeModels[$productType]->overrideAttribute($attribute)) {
                        break;
                    }
                }
            } else { // remove attributes of not-supported product types
                $collection->removeItemByKey($attribute->getId());
            }
        }
        return $collection;
    }

    /**
     * Entity attributes collection getter.
     *
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Attribute_Collection
     */
    public function getAttributeCollection()
    {
        return Mage::getResourceModel('catalog/product_attribute_collection');
    }

    /**
     * EAV entity type code getter.
     *
     * @return string
     */
    public function getEntityTypeCode()
    {
        return 'catalog_product';
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

}
