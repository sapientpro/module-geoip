<?php

namespace SapientPro\GeoIP\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use SapientPro\GeoIP\Api\Validator\GeoIpRedirectValidatorInterface;
use SapientPro\GeoIP\Model\Config;
use SapientPro\GeoIP\Model\StoreSwitcher;
use SapientPro\GeoIP\Service\GeoIpServiceProvider;

class Index extends Action
{
    /**
     * @var JsonFactory
     */
    private JsonFactory $jsonFactory;

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

    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
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
        CookieMetadataFactory $cookieMetadataFactory
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
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

    public function execute()
    {
        if (!$this->config->isEnabled()) {
            $result = $this->jsonFactory->create();
            $data = [
                'success' => false,
                'message' => 'Geoip redirect is disabled.',
            ];

            return $result->setData($data);
        }

        if (!$this->geoIpRedirectValidator->validate()) {
            $result = $this->jsonFactory->create();
            $data = [
                'success' => false,
                'message' => 'Geoip redirect is not valid.',
            ];

            return $result->setData($data);
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

                $result = $this->jsonFactory->create();
                $data = [
                    'success' => true,
                    'message' => 'Redirecting to ' . $storeTo->getName(),
                    'data' => [
                        'redirect_url' => $switchUrl,
                    ],
                ];

                return $result->setData($data);
            } catch (NoSuchEntityException $e) {
            }
        }

        $data = [
            'success' => true,
            'message' => 'Redirect not needed.',
            'data' => [
                'redirect_url' => $switchUrl,
            ],
        ];

        return $result->setData($data);
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
