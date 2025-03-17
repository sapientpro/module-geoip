<?php

namespace SapientPro\GeoIP\Api\Provider;

interface GeoIpServiceProviderInterface
{
    /**
     * Get the name of the provider
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get the country by IP address
     *
     * @param string $ipAddress
     * @return string|null
     */
    public function getCountry(string $ipAddress): ?string;
}
