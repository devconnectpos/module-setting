<?php
namespace SM\Setting\Repositories;

use Exception;
use Magento\Catalog\Model\Product;
use Magento\Config\Model\Config\Loader;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use SM\Core\Api\Data\RetailConfig;
use SM\Core\Api\Data\XSetting;
use SM\Core\Model\DataObject;
use SM\CustomSale\Helper\Data;
use SM\Integrate\Helper\Data as IntegrateHelper;
use SM\Integrate\Model\GCIntegrateManagement;
use SM\Product\Helper\ProductHelper;
use SM\XRetail\Helper\DataConfig;
use SM\XRetail\Repositories\Contract\ServiceAbstract;

/**
 * Class SettingManagement
 *
 * @package SM\Setting\Repositories
 */
class SettingManagement extends ServiceAbstract
{

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;
    /**
     * @var \Magento\Config\Model\Config\Loader
     */
    protected $configLoader;
    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    protected $configResource;

    /**
     * @var \SM\CustomSale\Helper\Data
     */
    protected $customSaleHelper;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    protected $configWriter;
    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    protected $cacheTypeList;
    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * @var \SM\Product\Helper\ProductHelper
     */
    private $productHelper;
    /**
     * @var \SM\Integrate\Helper\Data
     */
    protected $integrateHelperData;

    /**
     * @var \SM\Integrate\Model\GCIntegrateManagement
     */
    private $gcIntegrateManagement;

    /**
     * SettingManagement constructor.
     *
     * @param \Magento\Framework\App\RequestInterface               $requestInterface
     * @param \SM\XRetail\Helper\DataConfig                         $dataConfig
     * @param \Magento\Store\Model\StoreManagerInterface            $storeManager
     * @param \Magento\Framework\ObjectManagerInterface             $objectManager
     * @param \Magento\Config\Model\Config\Loader                   $loader
     * @param \Magento\Config\Model\ResourceModel\Config            $config
     * @param \SM\CustomSale\Helper\Data                            $customSaleHelper
     * @param \SM\Integrate\Helper\Data                             $integrateHelperData
     * @param \SM\Integrate\Model\GCIntegrateManagement             $GCIntegrateManagement
     * @param \SM\Product\Helper\ProductHelper                      $productHelper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface    $scopeConfig
     * @param \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
     * @param \Magento\Framework\App\Cache\TypeListInterface        $cacheTypeList
     * @param \Magento\Eav\Model\Config                             $eavConfig
     */
    public function __construct(
        RequestInterface $requestInterface,
        DataConfig $dataConfig,
        StoreManagerInterface $storeManager,
        ObjectManagerInterface $objectManager,
        Loader $loader,
        Config $config,
        Data $customSaleHelper,
        IntegrateHelper $integrateHelperData,
        GCIntegrateManagement $GCIntegrateManagement,
        ProductHelper $productHelper,
        ScopeConfigInterface $scopeConfig,
        WriterInterface $configWriter,
        TypeListInterface $cacheTypeList,
        \Magento\Eav\Model\Config $eavConfig
    ) {
        $this->configLoader          = $loader;
        $this->configResource        = $config;
        $this->objectManager         = $objectManager;
        $this->customSaleHelper      = $customSaleHelper;
        $this->integrateHelperData   = $integrateHelperData;
        $this->gcIntegrateManagement = $GCIntegrateManagement;
        $this->productHelper         = $productHelper;
        $this->integrateHelperData   = $integrateHelperData;
        $this->scopeConfig           = $scopeConfig;
        $this->configWriter          = $configWriter;
        $this->cacheTypeList         = $cacheTypeList;
        $this->eavConfig             = $eavConfig;
        parent::__construct($requestInterface, $dataConfig, $storeManager);
    }

    /**
     *
     * @throws \Exception
     */
    public function getSettingData()
    {
        $settings = [];
        if ($this->getSearchCriteria()->getData('currentPage') == 1) {
            // Các function get data liên quan đến store sẽ lấy theo store này.
            $store = $this->getSearchCriteria()->getData('storeId');
            $outletId = $this->getSearchCriteria()->getData('outletId');
            if (is_null($store)) {
                throw  new Exception("Must have param storeId");
            }
            $this->storeManager->setCurrentStore($store);

            foreach ($this->getSettingEntityCollection() as $item) {
                /** @var \SM\Setting\Repositories\SettingManagement\AbstractSetting $instance */
                $instance = $this->objectManager->create($item);
                $instance->setStore($store);
                if ($outletId) {
                    $instance->setOutletId($outletId);
                }
                $setting = new XSetting();
                $setting->setData('key', $instance->getCODE());
                $setting->setData('value', $instance->build());
                $settings[] = $setting;
            }
        }

        return $this->getSearchResult()
                    ->setSearchCriteria($this->getSearchCriteria())
                    ->setItems($settings)
                    ->setLastPageNumber(1)
                    ->getOutput();
    }

    /**
     * @return array
     */
    protected function getSettingEntityCollection()
    {
        return [
            '\SM\Setting\Repositories\SettingManagement\Tax',
            '\SM\Setting\Repositories\SettingManagement\Shipping',
            '\SM\Setting\Repositories\SettingManagement\Customer',
            '\SM\Setting\Repositories\SettingManagement\Product',
            '\SM\Setting\Repositories\SettingManagement\Store'
        ];
    }

    /**
     * @return array
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function getRetailSettingData()
    {
        $searchCriteria = $this->getSearchCriteria();

        $configs = [];
        if ($searchCriteria->getData('currentPage') <= 1) {
            $config     = [];
            $configData = $this->configLoader->getConfigByPath('xretail/pos', 'default', 0);
            foreach ($configData as $configDatum) {
                $config[$configDatum['path']] = $this->convertValue($configDatum['value']);
            }

            $config["productAttributes"] = $this->productHelper->getProductAttributes();
            if ($this->integrateHelperData->isAHWGiftCardExist()) {
                $config['list_code_pools'] = $this->gcIntegrateManagement->getGCCodePool();
            }

            $config["xretail/pos/integrate_wh"] = "none";

            if (!!$this->integrateHelperData->isMagentoInventory()) {
                $config["xretail/pos/integrate_wh"] = "magento_inventory";
            }

            if (!!$this->integrateHelperData->isIntegrateWH()) {
                $config["xretail/pos/integrate_wh"] = "bms";
            }

            if (!!$this->integrateHelperData->isMagestoreInventory()) {
                $config["xretail/pos/integrate_wh"] = "mage_store";
            }

            $retailConfig = new RetailConfig();
            $retailConfig->setData('key', 'pos')->setData('value', $config);
            $configs[] = $retailConfig;
        }

        return $this->getSearchResult()->setItems($configs)->getOutput();
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function saveRetailSettingData()
    {
        $configData = $this->getRequest()->getParam('data');
        foreach ($configData as $key => $value) {
            if (is_array($value)) {
                $value = json_encode($value);
            }
            $this->configWriter->save($key, $value, 'default', 0);
        }
        //FIX XRT :549 updateRefundToGCProduct custom sales tax class
        if (isset($configData['xretail/pos/custom_sale_tax_class'])) {
            $customSales = $this->customSaleHelper->getCustomSaleProduct();
            $customSales->setStoreId(0);
            $customSales->setData('tax_class_id', $configData['xretail/pos/custom_sale_tax_class']);

            try {
                $customSales->save();
            } catch (\Exception $e) {
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $logger = $objectManager->get('Psr\Log\LoggerInterface');
                $logger->critical("====> [CPOS] Failed to update custom sales when saving settings: {$e->getMessage()}");
                $logger->critical($e->getTraceAsString());
            }
        }
        if (isset($configData['xretail/pos/integrate_gc'])
            && $this->integrateHelperData->isAHWGiftCardExist()) {
            if ($configData['xretail/pos/integrate_gc'] == "aheadWorks") {
                $data = [];
                if (isset($configData['xretail/pos/is_use_default_codepool_pattern'])) {
                    $data['is_default_codepool_pattern'] = $configData['xretail/pos/is_use_default_codepool_pattern'];
                }
                if (isset($configData['xretail/pos/refund_gc_codepool'])) {
                    $data['code_pool'] = $configData['xretail/pos/refund_gc_codepool'];
                }
                $this->integrateHelperData->getGcIntegrateManagement()->updateRefundToGCProduct($data);
            }
        }
        // check saving reward point integration info
        if (isset($configData['xretail/pos/integrate_rp'])) {
            if ($configData['xretail/pos/integrate_rp'] === 'aheadWorks'
                && !$this->integrateHelperData->isAHWRewardPointsExist()) {
                throw new LocalizedException(
                    __('Module Aheadworks_RewardPoints is not found!')
                );
            } elseif ($configData['xretail/pos/integrate_rp'] === 'amasty'
                && !$this->integrateHelperData->isAmastyRewardPointsExist()) {
                throw new LocalizedException(
                    __('Module Amasty_Rewards cannot be found!')
                );
            } elseif ($configData['xretail/pos/integrate_rp'] === 'mage2_ee'
                      && !$this->integrateHelperData->isRewardPointMagento2EEExist()) {
                throw new LocalizedException(
                    __('Module Magento_Reward is not found!')
                );
            }
        }
        //check saving gift card integration info
        if (isset($configData['xretail/pos/integrate_gc'])) {
            if ($configData['xretail/pos/integrate_gc'] === 'aheadWorks') {
                if (!$this->integrateHelperData->isAHWGiftCardExist()) {
                    throw new LocalizedException(
                        __('Module Aheadworks_Giftcard is not found!')
                    );
                }

                if (isset($configData['xretail/pos/is_use_default_codepool_pattern'])
                    && !$configData['xretail/pos/is_use_default_codepool_pattern']
                    && !$configData['xretail/pos/refund_gc_codepool']) {
                    throw new LocalizedException(
                        __('No Code Pool found! Please go to backend and create Code Pool first.')
                    );
                }

            } elseif ($configData['xretail/pos/integrate_gc'] === 'mage2_ee'
                      && !$this->integrateHelperData->isGiftCardMagento2EE()) {
                throw new LocalizedException(
                    __('Module Magento_GiftCardAccount is not found!')
                );
            }
        }
        //check saving store pick up integration info
        if (isset($configData['xretail/pos/integrate_store_pick_up_extension'])) {
            if ($configData['xretail/pos/integrate_store_pick_up_extension'] === 'mageworx'
                && !$this->integrateHelperData->isExistMageWorx()) {
                throw new LocalizedException(
                    __('Module MageWorx_Locations is not found!')
                );
            }
        }
        //check saving rma integration info
        if (isset($configData['xretail/pos/integrate_rma_extension'])) {
            if ($configData['xretail/pos/integrate_rma_extension'] === 'aheadWorks'
                && !$this->integrateHelperData->isExistAheadWorksRMA()) {
                throw new LocalizedException(
                    __('Module Aheadworks_Rma is not found!')
                );
            }
        }

        //check show estimated availability
        if (isset($configData['xretail/pos/show_estimated_availability'])) {
            if ($configData['xretail/pos/show_estimated_availability']
                && isset($configData['xretail/pos/attribute_for_estimated_availability'])
                && !!$configData['xretail/pos/attribute_for_estimated_availability']) {
                if (!$this->isProductAttributeExists($configData['xretail/pos/attribute_for_estimated_availability'])) {
                    throw new LocalizedException(
                        __("The attribute '%1' does not exist!", $configData['xretail/pos/attribute_for_estimated_availability'])
                    );
                }
            }
        }

        //check saving order comment integration setting
	    if (isset($configData['xretail/pos/integrate_order_comment_extensions'])) {
		    if ($configData['xretail/pos/integrate_order_comment_extensions'] === 'boldCommerce'
			    && !$this->integrateHelperData->isExistBoldOrderComment()) {
			    throw new LocalizedException(
				    __('Module Bold_OrderComment is not found!')
			    );
		    }
	    }

        $this->searchCriteria = new DataObject(
            [
                'group'       => $this->getRequest()->getParam('group'),
                'currentPage' => 1
            ]
        );

        return $this->getRetailSettingData();
    }

    protected function convertValue($value)
    {
        if (!is_array($value) && !is_null($value)) {
            $result = json_decode($value);
            if (json_last_error()) {
                $result = $value;
            }

            return $result;
        }

        return $value;
    }

    /**
     * @param string $attribute
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function isProductAttributeExists($attribute)
    {
        $attr = $this->eavConfig->getAttribute(Product::ENTITY, $attribute);

        return ($attr && $attr->getId()) ? true : false;
    }
}
