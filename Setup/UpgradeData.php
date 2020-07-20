<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace SM\Setting\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{

    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        if (version_compare($context->getVersion(), '0.0.3', '<')) {
            $this->dummySettingCategories($setup);
            $this->addUseProductOnlineModeSetting($setup);
        }
        if (version_compare($context->getVersion(), '0.0.4', '<')) {
            $this->dummyIntergrateGCExtension($setup);
        }
        if (version_compare($context->getVersion(), '0.0.4', '<')) {
            $this->dummySelectSeller($setup);
        }
        if (version_compare($context->getVersion(), '0.0.5', '<')) {
            $this->addUseMagentoRecommendation($setup);
            $this->addFeaturedProductRecommendation($setup);
            $this->addOtherSettingSecondScreen($setup);
        }
        if (version_compare($context->getVersion(), '0.0.5', '<')) {
            $this->addIntegrateStoreCredit($setup);
        }

        if (version_compare($context->getVersion(), '0.0.6', '<')) {
            $this->addShowSaleTagOnProductSetting($setup);
        }

        if (version_compare($context->getVersion(), '0.0.7', '<')) {
            $this->dummySettingVeriface($setup);
        }

        if (version_compare($context->getVersion(), '0.0.8', '<')) {
            $this->dummySettingEnableDefaultCategory($setup);
        }

        if (version_compare($context->getVersion(), '0.0.9', '<')) {
            $this->dummyCposDefaultSettings($setup);
        }

        if (version_compare($context->getVersion(), '0.1.1', '<')) {
            $this->dummyAutoLockScreenSetting($setup);
        }
        if (version_compare($context->getVersion(), '0.1.2', '<')) {
            $this->dummyIntegrateCloudErp($setup);
        }

        if (version_compare($context->getVersion(), '0.1.3', '<')) {
            $this->dummyAllowCheckBundleOptionAvailabilitySetting($setup);
        }

        if (version_compare($context->getVersion(), '0.1.4', '<')) {
            $this->dummyIntegrateStorePickUpExtension($setup);
        }

        if (version_compare($context->getVersion(), '0.1.5', '<')) {
            $this->dummyPvfBundleProductSettings($setup);
        }

        if (version_compare($context->getVersion(), '0.1.6', '<')) {
            $this->dummyIntegrateRMAExtension($setup);
        }
        if (version_compare($context->getVersion(), '0.1.7', '<')) {
            $this->dummyTaxPercentAmountPrintLabel($setup);
        }
        if (version_compare($context->getVersion(), '0.1.8', '<')) {
            $this->dummyAllowRefundPendingOrderSetting($setup);
        }
        if (version_compare($context->getVersion(), '0.1.9', '<')) {
            $this->dummyIntegrateOrderCommentExtension($setup);
        }
        if (version_compare($context->getVersion(), '0.2.0', '<')) {
            $this->addSettingAutoDeductRewardPointsWhenRefundWithoutReceipt($setup);
        }

        if (version_compare($context->getVersion(), '0.2.1', '<')) {
            $this->addSettingForPrintLabel($setup);
        }
    }

    protected function dummySettingCategories(ModuleDataSetupInterface $setup)
    {
        $configData = $setup->getTable('core_config_data');
        $setup->getConnection()->insertArray(
            $configData,
            [
                'path',
                'value',
                'scope',
                'scope_id'
            ],
            [
                [
                    'path'     => "xretail/pos/use_large_categories",
                    'value'    => 0,
                    'scope'    => 'default',
                    'scope_id' => 0
                ],
                [
                    'path'     => "xretail/pos/display_selected_category",
                    'value'    => 'sub_categories_product',
                    'scope'    => 'default',
                    'scope_id' => 0
                ]
            ]
        );
    }


    protected function dummySelectSeller(ModuleDataSetupInterface $setup)
    {
        $configData = $setup->getTable('core_config_data');

        $data = [
            'path'     => "xretail/pos/allow_select_seller",
            'value'    => 0,
            'scope'    => 'default',
            'scope_id' => 0
        ];
        $setup->getConnection()->insertOnDuplicate($configData, $data, ['value']);
    }

    protected function dummyIntergrateGCExtension(ModuleDataSetupInterface $setup)
    {
        $configData = $setup->getTable('core_config_data');
        $data       = [
            'path'     => "xretail/pos/integrate_gc",
            'value'    => "none",
            'scope'    => 'default',
            'scope_id' => 0,
        ];
        $setup->getConnection()->insertOnDuplicate($configData, $data, ['value']);
    }

    protected function addUseProductOnlineModeSetting(ModuleDataSetupInterface $setup)
    {
        $configData = $setup->getTable('core_config_data');
        $data       = [
            'path'     => "xretail/pos/use_product_online_mode",
            'value'    => 0,
            'scope'    => 'default',
            'scope_id' => 0,
        ];
        $setup->getConnection()->insertOnDuplicate($configData, $data, ['value']);
    }

    protected function addUseMagentoRecommendation(ModuleDataSetupInterface $setup)
    {
        $configData = $setup->getTable('core_config_data');
        $data       = [
            'path'     => "xretail/pos/use_magento_recommendation",
            'value'    => 1,
            'scope'    => 'default',
            'scope_id' => 0,
        ];
        $setup->getConnection()->insertOnDuplicate($configData, $data, ['value']);
    }

    protected function addFeaturedProductRecommendation(ModuleDataSetupInterface $setup)
    {
        $configData = $setup->getTable('core_config_data');
        $data       = [
            'path'     => "xretail/pos/featured_product_recommendation",
            'value'    => json_encode([]),
            'scope'    => 'default',
            'scope_id' => 0,
        ];
        $setup->getConnection()->insertOnDuplicate($configData, $data, ['value']);
    }

    protected function addOtherSettingSecondScreen(ModuleDataSetupInterface $setup)
    {
        $configData = $setup->getTable('core_config_data');
        $setup->getConnection()->insertArray(
            $configData,
            [
                'path',
                'value',
                'scope',
                'scope_id'
            ],
            [
                [
                    'path'     => "xretail/pos/screensaver_mode_after",
                    'value'    => 0,
                    'scope'    => 'default',
                    'scope_id' => 0
                ],
                [
                    'path'     => "xretail/pos/change_screensaver_every",
                    'value'    => 0,
                    'scope'    => 'default',
                    'scope_id' => 0
                ],
                [
                    'path'     => "xretail/pos/picture_video_screensaver",
                    'value'    => json_encode([]),
                    'scope'    => 'default',
                    'scope_id' => 0
                ]
            ]
        );
    }

    protected function addIntegrateStoreCredit(ModuleDataSetupInterface $setup)
    {
        $this->dummySetting($setup, 'xretail/pos/integrate_store_credit', 'none');
    }

    protected function addShowSaleTagOnProductSetting(ModuleDataSetupInterface $setup)
    {
        $this->dummySetting($setup, 'xretail/pos/show_sales_tag', 1);
    }

    protected function dummySettingVeriface(ModuleDataSetupInterface $setup)
    {
        $this->dummySetting($setup, 'xretail/pos/veriface', 0);
        $this->dummySetting($setup, 'xretail/pos/veriface_username', '');
        $this->dummySetting($setup, 'xretail/pos/veriface_password', '');
        $this->dummySetting($setup, 'xretail/pos/veriface_token', '');
    }

    protected function dummySettingEnableDefaultCategory(ModuleDataSetupInterface $setup)
    {
        $this->dummySetting($setup, 'xretail/pos/enable_default_category', 0);
    }

    protected function dummyCposDefaultSettings(ModuleDataSetupInterface $setup)
    {
        $this->dummySetting($setup, 'xretail/pos/custom_sale_tax_class', 0);
        $this->dummySetting($setup, 'xretail/pos/sync_when_cart_changes', 0);
        $this->dummySetting($setup, 'xretail/pos/integrate_freegift', 'none');
        $this->dummySetting($setup, 'xretail/pos/allow_pending_order', 0);
    }

    protected function dummyAutoLockScreenSetting(ModuleDataSetupInterface $setup)
    {
        $this->dummySetting($setup, 'xretail/pos/auto_log_type', json_encode(["auto_log_per_minute"]));
        $this->dummySetting($setup, 'xretail/pos/auto_log_minutes', 5);
    }

    protected function dummyIntegrateCloudErp(ModuleDataSetupInterface $setup)
    {
        $this->dummySetting($setup, 'xretail/pos/integrate_cloud_erp', 'none');
    }

    protected function dummyAllowCheckBundleOptionAvailabilitySetting(ModuleDataSetupInterface $setup)
    {
        $this->dummySetting($setup, 'xretail/pos/allow_check_bundle_option_availability', 0);
    }

    protected function dummySetting(ModuleDataSetupInterface $setup, $path, $value)
    {
        $configData = $setup->getTable('core_config_data');
        $select     = $setup->getConnection()
                            ->select()
                            ->from($configData)
                            ->where('path = ?', $path);
        if (count($setup->getConnection()->fetchAll($select)) < 1) {
            $data = [
                'path'     => $path,
                'value'    => $value,
                'scope'    => 'default',
                'scope_id' => 0
            ];
            $setup->getConnection()->insertOnDuplicate($configData, $data, ['value']);
        }
    }

    protected function dummyIntegrateStorePickUpExtension(ModuleDataSetupInterface $setup)
    {
        $this->dummySetting($setup, 'xretail/pos/integrate_store_pick_up_extension', 'none');
    }

    protected function dummyPvfBundleProductSettings(ModuleDataSetupInterface $setup)
    {
        $this->dummySetting($setup, 'xretail/pos/allow_check_bundle_option_availability', 0);
        $this->dummySetting($setup, 'xretail/pos/show_estimated_availability', 0);
        $this->dummySetting($setup, 'xretail/pos/attribute_for_estimated_availability', '');
        $this->dummySetting($setup, 'xretail/pos/unit_type_for_estimated_availability', 'Days');
    }

    protected function dummyIntegrateRMAExtension(ModuleDataSetupInterface $setup)
    {
        $this->dummySetting($setup, 'xretail/pos/integrate_rma_extension', 'none');
    }

    protected function dummyTaxPercentAmountPrintLabel(ModuleDataSetupInterface $setup)
    {
        $this->dummySetting($setup, 'xretail/pos/tax_percent_amount', 10);
    }

    protected function dummyAllowRefundPendingOrderSetting(ModuleDataSetupInterface $setup)
    {
        $this->dummySetting($setup, 'xretail/pos/allow_refund_pending_order', 0);
    }
	
	protected function dummyIntegrateOrderCommentExtension(ModuleDataSetupInterface $setup)
	{
		$this->dummySetting($setup, 'xretail/pos/integrate_order_comment_extensions', 'none');
	}
	
	protected function addSettingAutoDeductRewardPointsWhenRefundWithoutReceipt(ModuleDataSetupInterface $setup)
	{
		$this->dummySetting($setup, 'xretail/pos/deduct_rp_when_refund_without_receipt', 0);
	}

    /**
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $setup
     */
    protected function addSettingForPrintLabel(ModuleDataSetupInterface $setup)
    {
        $this->dummySetting($setup, 'xretail/pos/print_label_barcode_type', 'CODE128');
        $this->dummySetting($setup, 'xretail/pos/print_label_barcode_attribute', 'sku');
        $this->dummySetting($setup, 'xretail/pos/print_label_unit_system', 'metric');
        $this->dummySetting($setup, 'xretail/pos/print_label_label_size', '35x22');
        $this->dummySetting($setup, 'xretail/pos/print_label_width', '35');
        $this->dummySetting($setup, 'xretail/pos/print_label_height', '22');
        $this->dummySetting($setup, 'xretail/pos/print_label_distance_between_labels', '3');
        $this->dummySetting($setup, 'xretail/pos/print_label_label_detail', json_encode(["sku", "product_name", "price", "date"]));
        $this->dummySetting($setup, 'xretail/pos/print_label_display_value', 0);
    }
}
