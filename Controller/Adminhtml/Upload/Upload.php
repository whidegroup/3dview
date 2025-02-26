<?php

namespace Whidegroup\VolumetricModels\Controller\Adminhtml\Upload;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Json;

class Upload extends Action
{
    /**
     * @var UploaderFactory
     */
    private UploaderFactory $fileUploader;
    /**
     * @var WriteInterface
     */
    private WriteInterface $mediaDirectory;
    /**
     * @var Filesystem
     */
    private Filesystem $filesystem;

    /**
     *
     * @param Context         $context
     * @param UploaderFactory $fileUploader
     * @param Filesystem      $filesystem
     * @throws FileSystemException
     */
    public function __construct(
        Action\Context $context,
        UploaderFactory $fileUploader,
        Filesystem $filesystem,
    ) {
        parent::__construct($context);
        $this->fileUploader = $fileUploader;
        $this->filesystem = $filesystem;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
    }

    /**
     * Uploads file to the media
     *
     * @return Json
     */
    public function execute(): Json
    {
        try {
            $uploader = $this->fileUploader->create(['fileId' => 'volumetric_model']);
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);
            $result = $uploader->save($this->mediaDirectory->getAbsolutePath('wg/models'));
            $result['file'] = 'wg/models' . $result['file'];
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }

        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }
}
