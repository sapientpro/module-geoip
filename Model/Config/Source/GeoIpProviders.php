<?php

namespace SapientPro\GeoIP\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use SapientPro\GeoIP\Api\GeoIpServiceInterface;

class GeoIpProviders implements OptionSourceInterface
{
    /**
     * @var GeoIpServiceInterface
     */
    private GeoIpServiceInterface $geoIpService;

    /**
     * GeoIpProviders Constructor
     *
     * @param GeoIpServiceInterface $geoIpService
     */
    public function __construct(GeoIpServiceInterface $geoIpService)
    {
        $this->geoIpService = $geoIpService;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        $options = [];

        foreach ($this->geoIpService->getAvailableProviders() as $provider) {
            $options[] = [
                'value' => $provider,
                'label' => $provider
            ];
        }

        return $options;
    }
}
