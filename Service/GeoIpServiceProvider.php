<?php

namespace SapientPro\GeoIP\Service;

use SapientPro\GeoIP\Api\GeoIpServiceInterface;

class GeoIpServiceProvider implements GeoIpServiceInterface
{
    /**
     * @var GeoIpServiceInterface
     */
    private GeoIpServiceInterface $provider;

    /**
     * @var array
     */
    private array $providers;

    /**
     * @param $scopeConfig
     * @param array $providers
     */
    public function __construct(
        $scopeConfig,
        array $providers
    ) {
        $this->providers = $providers;
    }

    public function getAvailableProviders(): array
    {
        return $this->providers;
    }

    public function getCountry(string $ipAddress): ?string
    {
        // TODO: Implement getCountry() method.
        return '';
    }

}
