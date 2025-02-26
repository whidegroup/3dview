<?php

namespace Whidegroup\VolumetricModels\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Whidegroup\VolumetricModels\Model\VolumeEntityModelFactory;
use Whidegroup\VolumetricModels\Model\VolumeIndexModelFactory;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\View\Asset\Repository;

class Data extends AbstractHelper
{
    /**
     * @var VolumeIndexModelFactory
     */
    private VolumeIndexModelFactory $_volumeIndexFactory;
    /**
     * @var VolumeEntityModelFactory
     */
    private VolumeEntityModelFactory $_volumeEntityFactory;
    /**
     * @var Repository
     */
    private Repository $assetRepository;

    /**
     *
     * @param Context                  $context
     * @param VolumeEntityModelFactory $volumeEntityFactory
     * @param VolumeIndexModelFactory  $volumeIndexFactory
     * @param ScopeConfigInterface     $scopeConfig
     * @param Repository               $assetRepository
     */
    public function __construct(
        Context                  $context,
        VolumeEntityModelFactory $volumeEntityFactory,
        VolumeIndexModelFactory  $volumeIndexFactory,
        ScopeConfigInterface     $scopeConfig,
        Repository               $assetRepository
    ) {
        parent::__construct($context);
        $this->_volumeEntityFactory = $volumeEntityFactory;
        $this->_volumeIndexFactory = $volumeIndexFactory;
        $this->scopeConfig = $scopeConfig;
        $this->assetRepository = $assetRepository;
    }

    /**
     * Returns array of the executive extensions of the 3D model
     *
     * @return mixed
     */
    public function getExecutiveExtensions(): array
    {
        return ["3dm", "3mf", "amf", "bvh", "gltf", "glb", "fbx", "stl", "max", "obj"];
    }

    /**
     * Returns value from the scope config
     *
     * @param string $configurationPath
     * @return mixed
     */
    private function getConfigValue(string $configurationPath): mixed
    {
        return $this->scopeConfig->getValue(
            $configurationPath,
            ScopeInterface::SCOPE_STORE,
        );
    }

    /**
     * Returns enable status of the module
     *
     * @return mixed
     */
    public function getModuleEnabled(): mixed
    {
        return $this->getConfigValue('volumeModel/general/enable');
    }

    /**
     * Returns global settings of the module
     *
     * @return mixed
     */
    public function getGlobalSettings(): mixed
    {
        return $this->getConfigValue('volumeModel/general');
    }
}
