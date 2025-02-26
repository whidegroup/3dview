<?php

namespace Whidegroup\VolumetricModels\Controller\Adminhtml\Delete;

use Exception;
use Magento\Backend\App\Action;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Serialize\SerializerInterface;
use Psr\Log\LoggerInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultInterface;
use Magento\Catalog\Model\Product;

class Delete extends Action
{

    /**
     * @var Filesystem\Driver\File
     */
    private Filesystem\Driver\File $_file;
    /**
     * @var ProductRepositoryInterface
     */
    private ProductRepositoryInterface $_productRepository;
    /**
     * @var SerializerInterface
     */
    private SerializerInterface $_serializer;
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $_logger;
    /**
     * @var Filesystem
     */
    private Filesystem $_filesystem;

    /**
     *
     * @param Context                    $context
     * @param File                       $file
     * @param ProductRepositoryInterface $productRepository
     * @param SerializerInterface        $_serializer
     * @param LoggerInterface            $logger
     * @param Filesystem                 $_filesystem
     */
    public function __construct(
        Action\Context             $context,
        Filesystem\Driver\File     $file,
        ProductRepositoryInterface $productRepository,
        SerializerInterface        $_serializer,
        LoggerInterface            $logger,
        Filesystem                 $_filesystem,
    ) {
        $this->_file = $file;
        $this->_productRepository = $productRepository;
        $this->_serializer = $_serializer;
        $this->_logger = $logger;
        $this->_filesystem = $_filesystem;
        parent::__construct($context);
    }

    /**
     * Removes 3D model from the product
     *
     * @return Json
     */
    public function execute(): Json
    {
        try {
            $postParams = $this->getRequest()->getPostValue();
            $productId = $postParams["productId"];
            $product = $this->_productRepository->getById($productId);
            $this->removeModelFolder($product);
            $product = $this->removeModuleEavAttribute($product->getData(), $product);
            $product->save();
            return $this->resultFactory->create(ResultFactory::TYPE_JSON)
                ->setData("3D model has been deleted successfully!");
        } catch (Exception $e) {
            $this->_logger->critical($e);
            return $this->resultFactory->create(ResultFactory::TYPE_JSON)
                ->setData("Error while deleting of the 3D model");
        }
    }

    /**
     * Check if folder exist and delete it
     *
     * @param string $modelDirectoryPath
     * @throws FileSystemException
     */
    private function removeFolderIfExists(string $modelDirectoryPath): void
    {
        if ($this->_file->isExists($modelDirectoryPath)) {
            $this->_file->deleteDirectory($modelDirectoryPath);
        }
    }

    /**
     * Remove all eav attributes of the module for product
     *
     * @param Product $product
     * @throws Exception
     */
    private function removeModelFolder(Product $product): void
    {
        $executionFileData = $product->getData("volume_model_executive_file");
        if (!isset($executionFileData)) {
            $productId = $product->getId();
            $executionFileData = $this->_filesystem
                ->getDirectoryRead(DirectoryList::MEDIA)
                ->getAbsolutePath('wg/models/' . $productId);
        }
        try {
            $executionFileData = $this->_serializer->unserialize($executionFileData);
            if (isset($executionFileData["dirname"])) {
                $this->removeFolderIfExists($executionFileData["dirname"]);
            }
        } catch (Exception $e) {
            $this->_logger->critical($e);
        }
    }

    /**
     * Remove all eav attributes of the module for product
     *
     * @param array   $data
     * @param Product $product
     *
     * @return Product
     */
    private function removeModuleEavAttribute(array $data, Product $product): Product
    {
        $moduleEavAttributes = array_filter($data, function ($v, $k) {
            return str_contains($k, "volume_model");
        }, ARRAY_FILTER_USE_BOTH);

        foreach ($moduleEavAttributes as $attribute => $value) {
            $product->setData($attribute, null);
        }

        return $product;
    }
}
