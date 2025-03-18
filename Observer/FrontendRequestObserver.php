<?php

namespace SapientPro\GeoIP\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use SapientPro\GeoIP\Service\GeoIpServiceProvider;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\HTTP\PhpEnvironment\Request;
use Magento\Framework\App\ResponseInterface;

class FrontendRequestObserver implements ObserverInterface
{
    /**
     * @var GeoIpServiceProvider
     */
    private GeoIpServiceProvider $geoIpServiceProvider;

    /**
     * @var Request
     */
    private Request $request;

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
     * FrontendRequestObserver constructor
     *
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param GeoIpServiceProvider $geoIpServiceProvider
     * @param Json $json
     * @param Request $request
     * @param ResponseInterface $response
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        GeoIpServiceProvider $geoIpServiceProvider,
        Json $json,
        Request $request,
        ResponseInterface $response
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->geoIpServiceProvider = $geoIpServiceProvider;
        $this->json = $json;
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * Execute observer
     *
     * @param Observer $observer
     * @return void
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer): void
    {
        $ipAddress = $this->request->getClientIp();
        $store = $this->storeManager->getStore();
        $routes = $this->scopeConfig->getValue('geo_ip/routes/routes');
        $routes = $this->json->unserialize($routes);
        $countryCode = $this->geoIpServiceProvider->getCountryCode($ipAddress);

        foreach ($routes as $route) {
            if ($route['from_country'] === $countryCode) {
                // Am I already here?
                if ($route['redirect_to'] !== $store->getId()) {
                    // Redirect to store view
                    $storeTo = $this->storeManager->getStore($route['redirect_to']);
                    $storeUrl = $storeTo->getBaseUrl();

                    $this->response->setRedirect($storeUrl)->sendResponse();
                    exit;
                }

                break;
            }
        }


    }
}
