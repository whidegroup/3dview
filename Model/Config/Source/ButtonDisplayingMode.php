<?php

namespace Whidegroup\VolumetricModels\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class ButtonDisplayingMode implements OptionSourceInterface
{
    /**
     * Returns array of the displaying mode options
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'productDetails', 'label' => __('In Product Details Block')],
            ['value' => 'fotoramaGallery', 'label' => __('In the Fotorama Gallery Thumbnails')],
            ['value' => 'both', 'label' => __('Both')],
        ];
    }
}
