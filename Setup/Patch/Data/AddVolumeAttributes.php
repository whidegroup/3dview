<?php

namespace Whidegroup\VolumetricModels\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Whidegroup\VolumetricModels\Model\Config\Source\EnvironmentSelect;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Whidegroup\VolumetricModels\Model\Product\Attribute\Backend\File;

class AddVolumeAttributes implements DataPatchInterface
{
    /**
     * @var EavSetupFactory
     */
    private EavSetupFactory $eavSetupFactory;

    /**
     * @var ModuleDataSetupInterface
     */
    private ModuleDataSetupInterface $moduleDataSetup;

    /**
     * Constructor
     *
     * @param EavSetupFactory $eavSetupFactory
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * Apply patch
     */
    public function apply(): void
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $attributes = [
            'volume_model_enabled' => [
                'group' => '3D Model',
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => '3D Model Enabled',
                'input' => 'boolean',
                'class' => '',
                'source' => Boolean::class,
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => '0',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'unique' => false,
                'apply_to' => 'simple,configurable',
                'used_in_product_listing' => true,
            ],
            'volume_model' => [
                'group' => '3D Model',
                'type' => 'varchar',
                'label' => '3D Model File',
                'input' => 'file',
                'backend' => File::class,
                'frontend' => '',
                'class' => '',
                'source' => '',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'unique' => false,
                'apply_to' => 'simple,configurable,grouped,virtual,bundle,downloadable',
                'used_in_product_listing' => true
            ],
            'volume_model_environment' => [
                'group' => '3D Model',
                'type' => 'varchar',
                'backend' => '',
                'frontend' => '',
                'label' => 'Environment of the scene for 3D Model',
                'input' => 'select',
                'class' => '',
                'source' => EnvironmentSelect::class,
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'unique' => false,
                'apply_to' => 'simple,configurable,grouped,virtual,bundle,downloadable',
                'used_in_product_listing' => true
            ],
            'volume_model_background' => [
                'group' => '3D Model',
                'type' => 'varchar',
                'backend' => '',
                'frontend' => '',
                'label' => 'Background of 3D viewer',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => '#CCCCCC',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'unique' => false,
                'apply_to' => 'simple,configurable,grouped,virtual,bundle,downloadable',
                'used_in_product_listing' => true
            ],
            'volume_model_executive_file' => [
                'group' => '3D Model',
                'type' => 'varchar',
                'backend' => '',
                'frontend' => '',
                'label' => 'Data of the execution file',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => false,
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'unique' => false,
                'apply_to' => 'simple,configurable,grouped,virtual,bundle,downloadable',
                'used_in_product_listing' => true
            ],
        ];

        foreach ($attributes as $attributeCode => $attributeParams) {
            $eavSetup->addAttribute(Product::ENTITY, $attributeCode, $attributeParams);
        }
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * Returns version of the Patch Data
     */
    public static function getVersion(): string
    {
        return '1.0.0';
    }
}
