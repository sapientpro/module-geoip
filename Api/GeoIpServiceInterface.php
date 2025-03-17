<?php

namespace SapientPro\GeoIP\Api;

interface GeoIpServiceInterface
{
    /**
     * Get the available providers
     *
     * @return array
     */
    public function getAvailableProviders(): array;

    /**
     * Get the country by IP address
     *
     * @param string $ipAddress
     * @return string|null
     */
    public function getCountry(string $ipAddress): ?string;
}
