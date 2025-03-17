<?php

namespace SapientPro\GeoIP\Service\Provider;

use SapientPro\GeoIP\Api\Provider\GeoIpServiceProviderInterface;

class IpGeoLocation implements GeoIpServiceProviderInterface
{
    /**
     * @inheirtdoc
     */
    public function getName(): string
    {
        return 'GeoIP';
    }

    /**
     * @inheirtdoc
     */
    public function getCountry(string $ipAddress): ?string
    {
        return '';
    }
}
