<?php

namespace SapientPro\GeoIP\Service\Provider;

use SapientPro\GeoIP\Api\Provider\GeoIpServiceProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\HTTP\PhpEnvironment\Request;
use Magento\Framework\Encryption\EncryptorInterface;

class IpGeoLocation implements GeoIpServiceProviderInterface
{
    /**
     * API URL
     */
    private const API_URL = 'https://api.ipgeolocation.io/ipgeo?apiKey=';

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @var CacheInterface
     */
    private CacheInterface $cache;

    /**
     * @var Curl
     */
    private Curl $curl;

    /**
     * @var Json
     */
    private Json $json;

    /**
     * @var Request
     */
    private Request $request;

    /**
     * @var EncryptorInterface
     */
    private EncryptorInterface $encryptor;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Curl $curl,
        Json $json,
        Request $request,
        EncryptorInterface $encryptor,
        CacheInterface $cache
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->curl = $curl;
        $this->json = $json;
        $this->request = $request;
        $this->encryptor = $encryptor;
        $this->cache = $cache;
    }

    /**
     * @inheirtdoc
     */
    public function getName(): string
    {
        return 'ipGeoLocation';
    }

    /**
     * @inheirtdoc
     */
    public function getCountryCode(string $ipAddress): ?string
    {
        $data = $this->getApiResponse($ipAddress);

        return $data['country_code2'] ?? null;
    }

    /**
     * Get API response
     *
     * @param string $ipAddress
     * @return array
     */
    private function getApiResponse(string $ipAddress): array
    {
        $response = $this->cache->load($ipAddress);

        if (!$response) {
            $url = $this->getRequestUrl($ipAddress);

            $this->curl->addHeader('Content-Type', 'application/json');
            $this->curl->addHeader('Accept', 'application/json');
            $this->curl->addHeader('User-Agent', $this->request->getServer('HTTP_USER_AGENT'));
            $this->curl->get($url);

            $response = $this->curl->getBody();
            $this->cache->save($response, $ipAddress);
            return $this->json->unserialize($response);
        }

        return $this->json->unserialize($response);
    }

    /**
     * Get request URL
     *
     * @param string $ipAddress
     * @return string
     */
    private function getRequestUrl(string $ipAddress): string
    {
        $apiKey = $this->scopeConfig->getValue('geo_ip/general/ipgeolocation_apikey');
        $apiKey = $this->encryptor->decrypt($apiKey);
        return self::API_URL . $apiKey . '&ip=' . $ipAddress;
    }
}
