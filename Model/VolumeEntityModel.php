<?php
namespace Whidegroup\VolumetricModels\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

class VolumeEntityModel extends AbstractModel implements IdentityInterface
{
    /**
     * No route page id.
     */
    public const NOROUTE_ENTITY_ID = 'no-route';

    /**
     * Entity Id of the voolume entity
     */
    public const ENTITY_ID = 'entity_id';

    /**
     * VolumeEntity Blog cache tag.
     */
    public const CACHE_TAG = 'wg_volume_model';

    /**
     * @var string
     */
    protected $_cacheTag = 'wg_volume_model';

    /**
     * @var string
     */
    protected $_eventPrefix = 'wg_volume_model';

    /**
     * Dependency Initilization
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(ResourceModel\VolumeEntityModel::class);
    }

    /**
     * Load object data.
     *
     * @param int $id
     * @param string|null $field
     * @return $this
     */
    public function load($id, $field = null): static
    {
        if ($id === null) {
            return $this->noRoute();
        }
        return parent::load($id, $field);
    }

    /**
     * No Route
     *
     * @return $this
     */
    public function noRoute(): static
    {
        return $this->load(self::NOROUTE_ENTITY_ID, $this->getIdFieldName());
    }

    /**
     * Get Identities
     *
     * @return array
     */
    public function getIdentities(): array
    {
        return [self::CACHE_TAG.'_'.$this->getId()];
    }

    /**
     * Get Id
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return parent::getData(self::ENTITY_ID);
    }

    /**
     * Set Id
     *
     * @param int $id
     * @return VolumeEntityModel
     */
    public function setId($id): VolumeEntityModel
    {
        return $this->setData(self::ENTITY_ID, $id);
    }
}
