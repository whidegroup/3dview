<?php

namespace Whidegroup\VolumetricModels\Model;

use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\SerializerInterface;
use Whidegroup\VolumetricModels\Helper\Data;
use Magento\Framework\View\Asset\Repository;

class GetConfiguration
{
    /**
     * @var Repository
     */
    public Repository $assetRepo;
    /**
     * @var ProductRepository
     */
    private ProductRepository $productRepository;
    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;
    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;
    /**
     * @var Data
     */
    private Data $helper;

    /**
     * ProductRepositoryPlugin constructor.
     *
     * @param ProductRepository    $productRepository
     * @param Data                 $helper
     * @param SerializerInterface  $serializer
     * @param ScopeConfigInterface $scopeConfig
     * @param Repository           $assetRepo
     */
    public function __construct(
        ProductRepository $productRepository,
        Data $helper,
        SerializerInterface $serializer,
        ScopeConfigInterface $scopeConfig,
        Repository $assetRepo
    ) {
        $this->productRepository = $productRepository;
        $this->helper            = $helper;
        $this->serializer        = $serializer;
        $this->scopeConfig       = $scopeConfig;
        $this->assetRepo         = $assetRepo;
    }

    /**
     * Returns config array of the 3D model for product
     *
     * @param string $productId
     * @return bool|string
     * @throws NoSuchEntityException
     */
    public function getProductVolumeModelConfig(string $productId): bool|string
    {
        $configuration = $this->helper->getGlobalSettings();
        $product       = $this->productRepository->getById($productId);

        $resultArray = array_filter($product->getData(), function ($value, $key) {
            return str_starts_with($key, "volume_");
        }, ARRAY_FILTER_USE_BOTH);

        $resultArray = array_merge($resultArray, $configuration);
        $resultArray = $this->buildEnvironmentMapUrl($resultArray);

        return $this->serializer->serialize($resultArray);
    }

    /**
     * Returns URL of the environment map
     *
     * @param array $config
     * @return array
     */
    private function buildEnvironmentMapUrl(array $config): array
    {
        if (!isset($config["volume_model_environment"])) {
            return $config;
        }
        if ($config["volume_model_environment"] === "none") {
            return $config;
        }
        $fileId = "Whidegroup_VolumetricModels::environment/" . $config["volume_model_environment"] . "/texture.hdr";

        $config["volume_model_environment"] = $this->assetRepo->getUrlWithParams($fileId, ['area' => 'frontend']);
        return $config;
    }
}
