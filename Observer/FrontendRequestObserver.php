<?php

declare(strict_types=1);

namespace SapientPro\GeoIP\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use SapientPro\GeoIP\Service\GeoIpServiceProvider;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ResponseInterface;
use SapientPro\GeoIP\Model\Config;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use SapientPro\GeoIP\Api\Validator\GeoIpRedirectValidatorInterface;
use SapientPro\GeoIP\Model\StoreSwitcher;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;

class FrontendRequestObserver implements ObserverInterface
{
    /**
     * @var GeoIpServiceProvider
     */
    private GeoIpServiceProvider $geoIpServiceProvider;

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @var Json
     */
    private Json $json;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var ResponseInterface
     */
    private ResponseInterface $response;

    /**
     * @var Config
     */
    private Config $config;

    /**
     * @var RemoteAddress
     */
    private RemoteAddress $remoteAddress;

    /**
     * @var GeoIpRedirectValidatorInterface
     */
    private GeoIpRedirectValidatorInterface $geoIpRedirectValidator;

    /**
     * @var StoreSwitcher
     */
    private StoreSwitcher $storeSwitcher;
    /**
     * @var CookieManagerInterface
     */
    private CookieManagerInterface $cookieManager;
    /**
     * @var CookieMetadataFactory
     */
    private CookieMetadataFactory $cookieMetadataFactory;

    /**
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param GeoIpServiceProvider $geoIpServiceProvider
     * @param Json $json
     * @param ResponseInterface $response
     * @param Config $config
     * @param RemoteAddress $remoteAddress
     * @param GeoIpRedirectValidatorInterface $geoIpRedirectValidator
     * @param StoreSwitcher $storeSwitcher
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        GeoIpServiceProvider $geoIpServiceProvider,
        Json $json,
        ResponseInterface $response,
        Config $config,
        RemoteAddress $remoteAddress,
        GeoIpRedirectValidatorInterface $geoIpRedirectValidator,
        StoreSwitcher $storeSwitcher,
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->geoIpServiceProvider = $geoIpServiceProvider;
        $this->json = $json;
        $this->response = $response;
        $this->config = $config;
        $this->remoteAddress = $remoteAddress;
        $this->geoIpRedirectValidator = $geoIpRedirectValidator;
        $this->storeSwitcher = $storeSwitcher;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
    }

    /**
     * @param Observer $observer
     * @return void
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer): void
    {
        if (!$this->config->isEnabled()) {
            return;
        }

        if (!$this->geoIpRedirectValidator->validate()) {
            return;
        }

        $store = $this->storeManager->getStore();

        // Get visitor IP
        $visitorIP = $this->remoteAddress->getRemoteAddress();
        $routes = $this->scopeConfig->getValue('geo_ip/routes/routes');
        $routes = $this->json->unserialize($routes);
        $countryCode = $this->geoIpServiceProvider->getCountryCode($visitorIP);

        $redirectRoute = null;
        foreach ($routes as $route) {
            if ($route['from_country'] === $countryCode) {
                $redirectRoute = $route;
                break;
            }
            if ($route['from_country'] === 'any') {
                $redirectRoute = $route;
            }
        }

        if ($redirectRoute && $redirectRoute['redirect_to'] != $store->getId()) {
            try {
                $storeTo = $this->storeManager->getStore($redirectRoute['redirect_to']);
                $lastChangedTime = $this->config->getLastConfigChange();

                $switchUrl = $this->storeSwitcher->getSwitchStoreUrl($storeTo);

                $this->setLastChangedTimeCookie($lastChangedTime);

                $this->response->setRedirect($switchUrl)->sendResponse();
                exit;
            } catch (NoSuchEntityException $e) {
                return;
            }
        }
    }

    /**
     * Set the last config change time in a cookie
     *
     * @param string $lastChangedTime
     * @return void
     */
    private function setLastChangedTimeCookie(string $lastChangedTime): void
    {
        $metadata = $this->cookieMetadataFactory
            ->createPublicCookieMetadata()
            ->setDuration(GeoIpRedirectValidatorInterface::COOKIE_LIFETIME)
            ->setPath('/')
            ->setHttpOnly(false);

        $this->cookieManager->setPublicCookie(
            GeoIpRedirectValidatorInterface::COOKIE_NAME,
            $lastChangedTime,
            $metadata
        );
    }
}
