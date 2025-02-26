<?php
namespace Whidegroup\VolumetricModels\Model\ResourceModel\VolumeIndexModel;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Whidegroup\VolumetricModels\Model\VolumeIndexModel;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * Dependency Initilization
     *
     * @return void
     */
    public function _construct(): void
    {
        $this->_init(
            VolumeIndexModel::class,
            \Whidegroup\VolumetricModels\Model\ResourceModel\VolumeIndexModel::class
        );
        $this->_map['fields']['entity_id'] = 'main_table.entity_id';
    }
}
