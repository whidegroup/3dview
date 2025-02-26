<?php

namespace Whidegroup\VolumetricModels\Ui\DataProvider\Product\Form\Modifier;

use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\UrlInterface;

class File extends AbstractModifier
{
    /**
     * @var ArrayManager
     */
    protected ArrayManager $arrayManager;
    /**
     * @var PostHelper
     */
    protected PostHelper $_postDataHelper;
    /**
     * @var UrlInterface
     */
    private UrlInterface $urlBuilder;

    /**
     * @param ArrayManager $arrayManager
     * @param PostHelper $_postDataHelper
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        ArrayManager $arrayManager,
        PostHelper   $_postDataHelper,
        UrlInterface $urlBuilder
    ) {
        $this->arrayManager    = $arrayManager;
        $this->_postDataHelper = $_postDataHelper;
        $this->urlBuilder      = $urlBuilder;
    }

    /**
     * Add Actions section to the 3D model section
     *
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta): array
    {
        $fieldCode     = 'volume_model';
        $postDataUrl = $this->urlBuilder->getUrl("whidegroup_volumetricmodels/delete/delete");
        $containerPath = $this->arrayManager->findPath(static::CONTAINER_PREFIX . $fieldCode, $meta, null, 'children');

        if (!$containerPath) {
            return $meta;
        }

        $meta = $this->arrayManager->merge(
            $containerPath,
            $meta,
            [
                'children' => [
                    'button_delete' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'componentType'          => 'container',
                                    'elementTmpl'            => 'Whidegroup_VolumetricModels/form/element/actions',
                                    'component'              => 'Whidegroup_VolumetricModels/js/actions',
                                    'buttonClasses'          => 'VolumetricModels-delete-button',
                                    'visible'                => true,
                                    'dataScope'              => $fieldCode,
                                    'postDataUrl'            => $postDataUrl,
                                    'buttonTextClass'        => 'VolumetricModels-delete-button',
                                    'statusMessageElementId' => 'VolumetricModels-delete-status',
                                    'label'                  => 'Actions',
                                    'labelVisible'           => true,
                                    'sortOrder'              => 20
                                ],
                            ],
                        ],
                    ],
                    $fieldCode      => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'elementTmpl'       => 'Whidegroup_VolumetricModels/form/element/file',
                                    'component'         => 'Whidegroup_VolumetricModels/js/file',
                                    'sortOrder'         => 30,
                                    'additionalClasses' => 'volumetric_models__file',
                                    'visible'           => true
                                ]
                            ]
                        ]
                    ]
                ],
            ]
        );
        return $meta;
    }

    /**
     * Override base method
     *
     * @param array $data
     * @return array
     */
    public function modifyData(array $data): array
    {
        return $data;
    }
}
