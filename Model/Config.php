<?php

declare(strict_types=1);

namespace SapientPro\GeoIP\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Config
{
    public const XML_PATH_ENABLED = 'geo_ip/general/is_active';
    public const XML_PATH_PROVIDER = 'geo_ip/general/provider';
    public const XML_PATH_API_KEY = 'geo_ip/general/ipgeolocation_apikey';
    public const XML_PATH_LAST_CONFIG_CHANGE = 'geo_ip/general/last_config_change';

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * Config Constructor
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Check if GeoIP is enabled
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH_ENABLED);
    }

    /**
     * Get GeoIP provider
     *
     * @return string
     */
    public function getProvider(): string
    {
        return (string)$this->scopeConfig->getValue(self::XML_PATH_PROVIDER);
    }

    /**
     * Get GeoIP API key
     *
     * @return string
     */
    public function getIpGeolocationApikey(): string
    {
        return (string)$this->scopeConfig->getValue(self::XML_PATH_API_KEY) ?? '';
    }

    /**
     * Get last config change date
     *
     * @return string
     */
    public function getLastConfigChange(): string
    {
        return (string)$this->scopeConfig->getValue(self::XML_PATH_LAST_CONFIG_CHANGE) ?? '';
    }
}
