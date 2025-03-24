<?php

namespace SapientPro\GeoIP\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use SapientPro\GeoIP\Service\GeoIpServiceProvider;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ResponseInterface;
use SapientPro\GeoIP\Model\Config;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\UrlInterface;
use SapientPro\GeoIP\Api\Validator\GeoIpRedirectValidatorInterface;

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
     * @var CustomerSession
     */
    private CustomerSession $customerSession;

    /**
     * @var UrlInterface
     */
    private UrlInterface $url;

    /**
     * @var GeoIpRedirectValidatorInterface
     */
    private GeoIpRedirectValidatorInterface $geoIpRedirectValidator;

    /**
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param GeoIpServiceProvider $geoIpServiceProvider
     * @param Json $json
     * @param ResponseInterface $response
     * @param Config $config
     * @param RemoteAddress $remoteAddress
     * @param CustomerSession $customerSession
     * @param UrlInterface $url
     * @param GeoIpRedirectValidatorInterface $geoIpRedirectValidator
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        GeoIpServiceProvider $geoIpServiceProvider,
        Json $json,
        ResponseInterface $response,
        Config $config,
        RemoteAddress $remoteAddress,
        CustomerSession $customerSession,
        UrlInterface $url,
        GeoIpRedirectValidatorInterface $geoIpRedirectValidator
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->geoIpServiceProvider = $geoIpServiceProvider;
        $this->json = $json;
        $this->response = $response;
        $this->config = $config;
        $this->remoteAddress = $remoteAddress;
        $this->customerSession = $customerSession;
        $this->url = $url;
        $this->geoIpRedirectValidator = $geoIpRedirectValidator;
    }

    /**
     * @param Observer $observer
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
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
        $currentUrl = $this->url->getCurrentUrl();
        $baseUrl = $store->getBaseUrl();

        // Check if the user is on the base URL
        if (rtrim($currentUrl, '/') !== rtrim($baseUrl, '/')) {
            return; // Don't redirect if the user is not on the base URL
        }

        // Get visitor IP
        $visitorIP = $this->remoteAddress->getRemoteAddress();
        $routes = $this->scopeConfig->getValue('geo_ip/routes/routes');
        $routes = $this->json->unserialize($routes);
        $countryCode = $this->geoIpServiceProvider->getCountryCode($visitorIP);

        foreach ($routes as $route) {
            if ($route['from_country'] === $countryCode) {
                if ($route['redirect_to'] !== $store->getId()) {
                    $lastChangedTime = $this->config->getLastConfigChange();
                    $storeTo = $this->storeManager->getStore($route['redirect_to']);
                    $storeUrl = $storeTo->getBaseUrl();

                    // Set the session flag and update the last config change time in the session
                    $this->customerSession->setGeoRedirected(true);
                    $this->customerSession->setLastConfigChange($lastChangedTime);

                    // Redirect to the new store's base URL
                    $this->response->setRedirect($storeUrl)->sendResponse();
                    exit;
                }
                break;
            }
        }
    }
}
