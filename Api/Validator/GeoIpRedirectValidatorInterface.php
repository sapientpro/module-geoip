<?php

namespace SapientPro\GeoIP\Api\Validator;

interface GeoIpRedirectValidatorInterface
{
    /**
     * @return bool
     */
    public function validate(): bool;
}
