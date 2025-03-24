<?php

namespace SapientPro\GeoIP\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Api\Data\StoreInterface;

class StoreSwitcher
{
    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;
    /**
     * @var UrlInterface
     */
    private UrlInterface $url;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param UrlInterface $url
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        UrlInterface $url
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->url = $url;
    }

    /**
     * @param StoreInterface $store
     * @return string
     */
    public function getSwitchStoreUrl(StoreInterface $store): string
    {
        $currentUrl = $this->url->getCurrentUrl();

        if (!$this->scopeConfig->isSetFlag('web/url/use_store')) {
            // Encode current URL for safe redirection
            $encodedUrl = strtr(base64_encode($currentUrl), '+/=', '-_,');

            // Generate the store switch URL
            $switchUrl = $this->url->getUrl('stores/store/redirect', [
                '_query' => [
                    '___store' => $store->getCode(),
                    '___from_store' => $store->getCode(),
                    'uenc' => $encodedUrl
                ]
            ]);
        } else {
            $switchUrl = $store->getBaseUrl();
        }

        return $switchUrl;
    }
}
