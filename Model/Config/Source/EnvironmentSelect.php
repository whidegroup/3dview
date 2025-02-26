<?php

namespace Whidegroup\VolumetricModels\Model\Config\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class EnvironmentSelect extends AbstractSource
{
    /**
     * Returns array of the environment options
     *
     * @return array
     */
    public function getAllOptions(): array
    {
        $this->_options = [
            ['value' => 'none', 'label' => __('Static Color')],
            ['value' => 'mall', 'label' => __('Mall')],
            ['value' => 'passage', 'label' => __('Passage')],
            ['value' => 'room', 'label' => __('Room')],
            ['value' => 'lamps', 'label' => __('Fluorescent Lamps Studio')],
            ['value' => 'market', 'label' => __('Covered Market')]
        ];
        return $this->_options;
    }
}
