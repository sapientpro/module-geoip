<?php

namespace SapientPro\GeoIP\Api\Validator;

interface GeoIpRedirectValidatorInterface
{
    public const COOKIE_NAME = 'geo_redirect_last_change';
    public const COOKIE_LIFETIME = 86400; // 1 day

    /**
     * @return bool
     */
    public function validate(): bool;
}
