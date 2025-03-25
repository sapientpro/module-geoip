<?php

declare(strict_types=1);

namespace SapientPro\GeoIP\Service\Validator;

use SapientPro\GeoIP\Api\Validator\GeoIpRedirectValidatorInterface;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use SapientPro\GeoIP\Model\Config;

class GeoIpRedirectValidator implements GeoIpRedirectValidatorInterface
{
    /**
     * @var CookieManagerInterface
     */
    private CookieManagerInterface $cookieManager;

    /**
     * @var CookieMetadataFactory
     */
    private CookieMetadataFactory $cookieMetadataFactory;

    /**
     * @var Config
     */
    private Config $config;

    /**
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param Config $config
     */
    public function __construct(
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        Config $config
    ) {
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->config = $config;
    }

    /**
     * @return bool
     */
    public function validate(): bool
    {
        $lastChangedTime = $this->config->getLastConfigChange();
        $cookieLastChangedTime = $this->cookieManager->getCookie(self::COOKIE_NAME);

        if ($cookieLastChangedTime && $cookieLastChangedTime === $lastChangedTime) {
            return false;
        }

        return true;
    }
}
