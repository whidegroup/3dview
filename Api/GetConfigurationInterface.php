<?php
namespace Whidegroup\VolumetricModels\Api;

interface GetConfigurationInterface
{
    /**
     * GET for Post api
     *
     * @param string $productId
     * @return string
     */

    public function getProductVolumeModelConfig(string $productId): string;
}
