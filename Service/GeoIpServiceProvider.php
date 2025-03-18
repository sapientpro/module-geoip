<?php

namespace SapientPro\GeoIP\Service;

use SapientPro\GeoIP\Api\Provider\GeoIpServiceProviderInterface;
use SapientPro\GeoIP\Api\GeoIpServiceInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class GeoIpServiceProvider implements GeoIpServiceInterface
{
    /**
     * @var GeoIpServiceProviderInterface
     */
    private GeoIpServiceProviderInterface $provider;

    /**
     * @var array
     */
    private array $providers;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param array $providers
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        array $providers
    ) {
        $this->providers = $providers;
        $providerCode = $scopeConfig->getValue('geo_ip/general/provider');
        foreach ($providers as $provider) {
            if ($provider->getName() === $providerCode) {
                $this->provider = $provider;
                break;
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function getAvailableProviders(): array
    {
        return $this->providers;
    }

    /**
     * @inheritDoc
     */
    public function getCountryCode(string $ipAddress): ?string
    {
        return $this->provider->getCountryCode($ipAddress);
    }

}
