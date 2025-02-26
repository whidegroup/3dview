<?php

namespace Whidegroup\VolumetricModels\Plugin;

use Closure;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\NoSuchEntityException;

class ProductRepositoryPlugin
{
    /**
     * @var Filesystem
     */
    private Filesystem $_filesystem;
    /**
     * @var File
     */
    private File $_file;
    /**
     * @var ProductRepository
     */
    private ProductRepository $productRepository;

    /**
     * ProductRepositoryPlugin constructor.
     *
     * @param File $file
     * @param Filesystem $filesystem
     * @param ProductRepository $productRepository
     */
    public function __construct(
        File              $file,
        Filesystem        $filesystem,
        ProductRepository $productRepository,
    ) {
        $this->_file = $file;
        $this->_filesystem = $filesystem;
        $this->productRepository = $productRepository;
    }

    /**
     * Plugin method to delete volume model folder on product delete.
     *
     * @param ProductRepositoryInterface $subject
     * @param Closure $proceed
     * @param ProductInterface $product
     * @return mixed
     * @throws FileSystemException
     * @throws NoSuchEntityException
     */
    public function aroundDelete(
        ProductRepositoryInterface $subject,
        Closure                    $proceed,
        ProductInterface           $product
    ): mixed {
        $path = $this->_filesystem
            ->getDirectoryRead(DirectoryList::MEDIA)
            ->getAbsolutePath('wg/models/');

        $productId = $product->getEntityId();
        $product = $this->productRepository->getById($productId);
        $productId = $product->getId();
        $volumeModelPath = $path . $productId;

        $result = $proceed($product, false);

        if ($this->_file->isExists($volumeModelPath) && isset($productId)) {
            $this->_file->deleteDirectory($volumeModelPath);
        }

        return $result;
    }
}
