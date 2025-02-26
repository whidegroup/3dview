<?php

namespace Whidegroup\VolumetricModels\Model\Product\Attribute\Backend;

use Exception;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\File\Uploader as UploaderAlias;
use Magento\Framework\Filesystem;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Shell;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Psr\Log\LoggerInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Whidegroup\VolumetricModels\Helper\Data;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Filesystem\Glob;
use Magento\Framework\Filesystem\Io\File as IoFile;
use Magento\Framework\App\RequestInterface;

class File extends AbstractBackend
{
    /**
     * @var Filesystem\Driver\File
     */
    protected Filesystem\Driver\File $_file;

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $_logger;

    /**
     * @var Filesystem
     */
    protected Filesystem $_filesystem;

    /**
     * @var UploaderFactory
     */
    protected UploaderFactory $_fileUploaderFactory;
    /**
     * @var Data
     */
    private Data $volumeModelHelper;
    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;
    /**
     * @var ManagerInterface
     */
    private ManagerInterface $messageManager;
    /**
     * @var DataObject
     */
    private DataObject $_targetObject;
    /**
     * @var IoFile
     */
    private IoFile $ioFile;
    /**
     * @var RequestInterface
     */
    private RequestInterface $_request;

    /**
     * @var Shell
     */
    private Shell $shell;

    /**
     * Construct
     *
     * @param LoggerInterface $logger
     * @param Filesystem $filesystem
     * @param Filesystem\Driver\File $file
     * @param UploaderFactory $fileUploaderFactory
     * @param Data $volumeModelHelper
     * @param SerializerInterface $serializer
     * @param ManagerInterface $messageManager
     * @param IoFile $ioFile
     * @param RequestInterface $request
     * @param Shell $shell
     */
    public function __construct(
        LoggerInterface        $logger,
        Filesystem             $filesystem,
        Filesystem\Driver\File $file,
        UploaderFactory        $fileUploaderFactory,
        Data                   $volumeModelHelper,
        SerializerInterface    $serializer,
        ManagerInterface       $messageManager,
        IoFile                 $ioFile,
        RequestInterface       $request,
        Shell                  $shell
    ) {
        $this->_file = $file;
        $this->_filesystem = $filesystem;
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->_logger = $logger;
        $this->volumeModelHelper = $volumeModelHelper;
        $this->serializer = $serializer;
        $this->messageManager = $messageManager;
        $this->ioFile = $ioFile;
        $this->_request = $request;
        $this->shell = $shell;
    }

    /**
     * Method for saving volume module data to the database and media storage
     *
     * @param DataObject $object
     * @return File
     */
    public function afterSave($object): File|static
    {
        try {
            $this->_targetObject = $object;
            $files = $this->_request->getFiles('product');
            if (empty($files[$this->getAttribute()->getName()])) {
                return $this;
            }

            $path = $this->_filesystem
                ->getDirectoryRead(DirectoryList::MEDIA)
                ->getAbsolutePath('wg/models/');

            $uploader = $this->_fileUploaderFactory->create([
                'fileId' => 'product[' . $this->getAttribute()->getName() . ']'
            ]);

            $uploadedFileExtension = $uploader->getFileExtension();

            if ($uploadedFileExtension === "zip" || $uploadedFileExtension === "rar") {
                return $this->unpackAndSaveModelFromArchive($path, $uploadedFileExtension);
            }
            return $this->saveModelFile($path);

        } catch (Exception $e) {
            if ($e->getCode() != UploaderAlias::TMP_NAME_EMPTY) {
                $this->_logger->critical($e);
                $this->messageManager->addErrorMessage(
                    __('An error occurred while saving the 3D model of product: %1', $e->getMessage())
                );
            }
        }

        return $this;
    }

    /**
     * Method for saving data to eav attributes
     *
     * @param string $executiveFilePath
     * @param array $executionFileData
     * @throws Exception
     */
    private function saveModelDataToEav(string $executiveFilePath, array $executionFileData): void
    {
        $this->_targetObject->setData($this->getAttribute()->getName(), strstr($executiveFilePath, "wg"));
        $this->_targetObject->setData("volume_model_executive_file", $this->serializer->serialize($executionFileData));

        $this->getAttribute()->getEntity()->saveAttribute($this->_targetObject, $this->getAttribute()->getName());
        $this->getAttribute()->getEntity()->saveAttribute($this->_targetObject, "volume_model_executive_file");
    }

    /**
     *  Check if optimize scripts exists in the module directory.
     *
     * @throws FileSystemException
     */
    private function isOptimizeScriptsExists(): bool {
        $moduleScriptsPath = BP . '/app/code/Whidegroup/VolumetricModels/scripts';
        if (!$this->_file->isExists($moduleScriptsPath)) {
            return false;
        }
        return true;
    }

    /**
     * Get paths for Node.js and gltf-transform CLI.
     *
     * @return array
     */
    private function getGltfTransformPaths(): array
    {
        $moduleDir = BP . '/app/code/Whidegroup/VolumetricModels/scripts';

        return [
            'nodePath' => $moduleDir . '/bin/node',
            'gltfTransformPath' => $moduleDir . '/node_modules/.bin/gltf-transform',
        ];
    }

    /**
     * Optimize model file if applicable.
     *
     * @param string $filePath
     * @return void
     * @throws FileSystemException
     */
    private function optimize(string $filePath): void
    {
        if (!$this->isOptimizeScriptsExists()) {
            return;
        }
        $pathInfo = $this->ioFile->getPathInfo($filePath);
        $extension = strtolower($pathInfo['extension']);

        if ($extension === 'gltf' || $extension === 'glb') {
            $this->optimizeGltf($filePath);
        }
    }

    /**
     * Execute a shell command.
     *
     * @param string $command
     * @throws Exception
     */
    private function executeCommand(string $command): void
    {
        $this->shell->execute($command);
    }

    /**
     * Optimize GLTF model using gltf-transform CLI.
     *
     * @param string $filePath
     * @return void
     */
    private function optimizeGltf(string $filePath): void
    {
        try {
            $paths = $this->getGltfTransformPaths();
            $nodePath = $paths['nodePath'];
            $gltfTransformPath = $paths['gltfTransformPath'];

            $outputPath = $filePath;

            $command = sprintf(
                '%s %s optimize %s %s',
                escapeshellarg($nodePath),
                escapeshellarg($gltfTransformPath),
                escapeshellarg($filePath),
                escapeshellarg($outputPath)
            );

            $this->executeCommand($command);
        } catch (Exception $e) {
            $this->_logger->critical($e->getMessage());
            $this->messageManager->addErrorMessage(__('An error occurred during GLTF optimization: %1', $e->getMessage()));
        }
    }

    /**
     * Apply metal-rough transformation using gltf-transform CLI.
     *
     * @param string $filePath
     * @return void
     * @throws FileSystemException
     */
    private function applyMetalRough(string $filePath): void
    {
        if (!$this->isOptimizeScriptsExists()) {
            return;
        }
        try {
            $paths = $this->getGltfTransformPaths();
            $nodePath = $paths['nodePath'];
            $gltfTransformPath = $paths['gltfTransformPath'];

            $outputPath = $filePath;

            $command = sprintf(
                '%s %s metalrough %s %s',
                escapeshellarg($nodePath),
                escapeshellarg($gltfTransformPath),
                escapeshellarg($filePath),
                escapeshellarg($outputPath)
            );

            $this->executeCommand($command);
        } catch (Exception $e) {
            $this->_logger->critical($e->getMessage());
            $this->messageManager->addErrorMessage(__('An error occurred during Metal-Rough transformation: %1', $e->getMessage()));
        }
    }


    /**
     * Method for saving file of the 3D model
     *
     * @param string $path
     * @return File
     * @throws FileSystemException
     */
    private function saveModelFile(string $path): File
    {
        $modelDirectoryPath = $path . $this->_targetObject->getData('entity_id');
        $this->createOrUpdateModelFolder($modelDirectoryPath);
        $uploader = $this->_fileUploaderFactory->create([
            'fileId' => 'product[' . $this->getAttribute()->getName() . ']'
        ]);
        $uploader->setAllowRenameFiles(true);
        $uploader->save($modelDirectoryPath);

        $executionFileData = $this->searchExecutiveFile($modelDirectoryPath);

        $this->applyMetalRough($executionFileData["dirname"] . '/' . $executionFileData['basename']);
        $this->optimize($executionFileData["dirname"] . '/' . $executionFileData['basename']);

        $this->saveModelDataToEav($modelDirectoryPath, $executionFileData);

        return $this;
    }

    /**
     * Unpack archive and save model
     *
     * @param string $path
     * @param string $uploadedFileExtension
     * @return File
     * @throws FileSystemException
     */
    private function unpackAndSaveModelFromArchive(string $path, string $uploadedFileExtension): static
    {
        $uploader = $this->_fileUploaderFactory->create([
            'fileId' => 'product[' . $this->getAttribute()->getName() . ']'
        ]);
        $uploader->setAllowRenameFiles(true);
        $uploader->save($path);
        $fileName = $uploader->getUploadedFileName();

        $modelDirectoryPath = $path . $this->_targetObject->getData('entity_id');
        $archiveDirectoryPath = $path . $fileName;
        $this->createOrUpdateModelFolder($modelDirectoryPath);

        if ($uploadedFileExtension === "zip") {
            $this->extractZipArchive($archiveDirectoryPath, $modelDirectoryPath);
        } elseif ($uploadedFileExtension === "rar") {
            $this->extractRarArchive($archiveDirectoryPath, $modelDirectoryPath);
        }

        $executiveFilePath = $this->getFileExecutionFolder($modelDirectoryPath);
        $executionFileData = $this->searchExecutiveFile($executiveFilePath);

        $this->applyMetalRough($executionFileData["dirname"] . '/' . $executionFileData['basename']);
        $this->optimize($executionFileData["dirname"] . '/' . $executionFileData['basename']);

        $this->removeModelArchive($archiveDirectoryPath);
        $this->saveModelDataToEav($executiveFilePath, $executionFileData);

        return $this;
    }

    /**
     * Returns folder with execution file
     *
     * @param string $directory
     * @return File
     */
    private function getFileExecutionFolder(string $directory): mixed
    {
        $it = new RecursiveDirectoryIterator($directory);

        foreach (new RecursiveIteratorIterator($it) as $file) {
            $pathInfo = $this->ioFile->getPathInfo($file);
            $isExecutiveFileExistInFolder = $this->searchExecutiveFile($pathInfo['dirname']);
            if (count($isExecutiveFileExistInFolder) > 0) {
                return $pathInfo['dirname'];
            }
        }
        return '';
    }

    /**
     * Search executive file
     *
     * @param string $directory
     * @return array
     */
    private function searchExecutiveFile(string $directory): array
    {
        $executiveExtension = $this->volumeModelHelper->getExecutiveExtensions();
        foreach ($executiveExtension as $extension) {
            $findByFormat = Glob::glob($directory . "/" . "*." . $extension);
            $findByUppercaseFormat = Glob::glob($directory . "/" . "*." . strtoupper($extension));
            if (count($findByFormat) > 0) {
                return $this->ioFile->getPathInfo($findByFormat[0]);
            }
            if (count($findByUppercaseFormat) > 0) {
                return $this->ioFile->getPathInfo($findByUppercaseFormat[0]);
            }
        }
        return [];
    }

    /**
     * Check if model directory exist and create if not
     *
     * @param string $modelDirectoryPath
     * @throws FileSystemException
     */
    private function createOrUpdateModelFolder(string $modelDirectoryPath): void
    {
        if ($this->_file->isExists($modelDirectoryPath)) {
            $this->_file->deleteDirectory($modelDirectoryPath);
        }
        $this->_file->createDirectory($modelDirectoryPath);
    }

    /**
     * Delete archive with model
     *
     * @param string $pathToFile
     * @throws FileSystemException
     */
    private function removeModelArchive(string $pathToFile): void
    {
        if ($this->_file->isExists($pathToFile)) {
            $this->_file->deleteFile($pathToFile);
        }
    }

    /**
     * Extract zip archive
     *
     * @param string $archive
     * @param string $directory
     */
    private function extractZipArchive(string $archive, string $directory): void
    {
        try {
            $zip = new \ZipArchive;
            if ($zip->open($archive) === true) {
                $zip->extractTo($directory);
                $zip->close();
            }
        } catch (Exception $e) {
            $this->_logger->critical($e);
        }
    }

    /**
     *  Extract rar archive
     *
     * @param string $archive
     * @param string $directory
     */
    private function extractRarArchive(string $archive, string $directory): void
    {
        try {
            $rar = \RarArchive::open($archive);
            $entries = $rar->getEntries();
            foreach ($entries as $entry) {
                $entry->extract($directory);
            }
            $rar->close();
        } catch (Exception $e) {
            $this->_logger->critical($e);
        }
    }
}
