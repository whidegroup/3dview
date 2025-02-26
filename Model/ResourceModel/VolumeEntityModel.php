<?php
namespace Whidegroup\VolumetricModels\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class VolumeEntityModel extends AbstractDb
{
    /**
     * Dependency Initilization
     *
     * @return void
     */
    public function _construct(): void
    {
        $this->_init("wg_volumetric_entity", "entity_id");
    }
}
