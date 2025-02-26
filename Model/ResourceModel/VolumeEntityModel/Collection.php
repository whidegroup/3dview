<?php
namespace Whidegroup\VolumetricModels\Model\ResourceModel\VolumeEntityModel;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Whidegroup\VolumetricModels\Model\VolumeEntityModel;

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
            VolumeEntityModel::class,
            \Whidegroup\VolumetricModels\Model\ResourceModel\VolumeEntityModel::class
        );
        $this->_map['fields']['entity_id'] = 'main_table.entity_id';
    }
}
