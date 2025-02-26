<?php

namespace Whidegroup\VolumetricModels\Block\Catalog\Product;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Helper\Product;
use Magento\Catalog\Model\ProductTypes\ConfigInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\Url\EncoderInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Whidegroup\VolumetricModels\Helper\Data;
use Whidegroup\VolumetricModels\Model\VolumeEntityModelFactory;

class View extends \Magento\Catalog\Block\Product\View
{
    /**
     * Override base method
     *
     * @param Context                                  $context
     * @param EncoderInterface                         $urlEncoder
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param StringUtils                              $string
     * @param Product                                  $productHelper
     * @param ConfigInterface                          $productTypeConfig
     * @param FormatInterface                          $localeFormat
     * @param Session                                  $customerSession
     * @param ProductRepositoryInterface               $productRepository
     * @param PriceCurrencyInterface                   $priceCurrency
     * @param Data                                     $volumeModelHelper
     * @param StoreManagerInterface                    $storeManager
     * @param array                                    $data
     */
    public function __construct(
        Context $context,
        EncoderInterface $urlEncoder,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        StringUtils $string,
        Product $productHelper,
        ConfigInterface $productTypeConfig,
        FormatInterface $localeFormat,
        Session $customerSession,
        ProductRepositoryInterface $productRepository,
        PriceCurrencyInterface $priceCurrency,
        Data $volumeModelHelper,
        StoreManagerInterface $storeManager,
        array $data
    ) {
        parent::__construct(
            $context,
            $urlEncoder,
            $jsonEncoder,
            $string,
            $productHelper,
            $productTypeConfig,
            $localeFormat,
            $customerSession,
            $productRepository,
            $priceCurrency,
            $data
        );
        $this->_volumeModelHelper = $volumeModelHelper;
        $this->_storeManager      = $storeManager;
    }

    /**
     * Returns path of the model for product
     *
     * @return mixed
     */
    public function getVolumetricModelPathOfProduct(): mixed
    {
        $product                = $this->getProduct();
        $productVolumeModelPath = $product->getData("volume_model");

        if (!isset($productVolumeModelPath)) {
            return null;
        }

        return $productVolumeModelPath;
    }

    /**
     * Returns URL of the volumetric model
     *
     * @return string|null
     */
    public function getVolumetricModelUrl(): ?string
    {
        $volumetricModelPath = $this->getVolumetricModelPathOfProduct();
        if (!isset($volumetricModelPath)) {
            return null;
        }

        try {
            $mediaUrl = $this->_storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
            return $mediaUrl . $volumetricModelPath . "/scene.gltf";
        } catch (NoSuchEntityException $e) {
            //TODO add correct catch body
            return "";
        }
    }

    /**
     * Returns Button displaying mode
     *
     * @return string|null
     */
    public function getButtonDisplayingMode(): ?string
    {
        $moduleSettings = $this->_volumeModelHelper->getGlobalSettings();
        if (isset($moduleSettings["button_displaying_mode"])) {
            return $moduleSettings["button_displaying_mode"];
        }
        return "productDetails";
    }

    /**
     * Returns environment texture of the product
     *
     * @param string $productId
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getEnvironmentTexture(string $productId): mixed
    {
        $product = $this->productRepository->getById($productId);
        return $product->getData("volume_model_environment");
    }

    /**
     * Returns enable status of the module
     *
     * @return mixed
     */
    public function getModuleEnabled(): mixed
    {
        $enabledStatus = $this->_volumeModelHelper->getModuleEnabled();
        if (!isset($enabledStatus) || !$enabledStatus) {
            return false;
        }
        return filter_var($enabledStatus, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Returns model enabling
     *
     * @param string $productId
     * @return false|mixed
     * @throws NoSuchEntityException
     */
    public function getVolumeModelEnabledByProduct(string $productId): mixed
    {
        $product      = $this->productRepository->getById($productId);
        $modelEnabled = $product->getData("volume_model_enabled");
        if (!isset($modelEnabled) || !$modelEnabled) {
            return false;
        }
        return filter_var($modelEnabled, FILTER_VALIDATE_BOOLEAN);
    }
}
