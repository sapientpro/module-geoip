<?php

declare(strict_types=1);

namespace SapientPro\GeoIP\Service\Validator;

use SapientPro\GeoIP\Api\Validator\GeoIpRedirectValidatorInterface;
use Magento\Customer\Model\Session as CustomerSession;
use SapientPro\GeoIP\Model\Config;

class GeoIpRedirectValidator implements GeoIpRedirectValidatorInterface
{
    /**
     * @var CustomerSession
     */
    private CustomerSession $customerSession;

    /**
     * @var Config
     */
    private Config $config;

    /**
     * @param CustomerSession $customerSession
     * @param Config $config
     */
    public function __construct(
        CustomerSession $customerSession,
        Config $config
    ) {
        $this->customerSession = $customerSession;
        $this->config = $config;
    }

    /**
     * @return bool
     */
    public function validate(): bool
    {
        $lastChangedTime = $this->config->getLastConfigChange();
        $sessionLastChangedTime = $this->customerSession->getLastConfigChange();

        if ($this->customerSession->getGeoRedirected() && $sessionLastChangedTime === $lastChangedTime) {
            return false;
        }

        return true;
    }
}
