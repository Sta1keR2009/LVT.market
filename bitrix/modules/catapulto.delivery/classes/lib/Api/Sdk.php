<?php


namespace Ipol\Catapulto\Api;


use Bitrix\Rest\Dictionary\Method;
use Error;
use Ipol\Catapulto\Api\Adapter\CurlAdapter;
use Ipol\Catapulto\Api\Entity\EncoderInterface;
use Ipol\Catapulto\Api\Methods\GeneralMethod;

/**
 * Class Sdk
 * @package Ipol\Catapulto\Api
 */
class Sdk
{
    /**
     * @var CurlAdapter
     */
    private $adapter;
    /**
     * @var EncoderInterface|null
     */
    private $encoder;
    /**
     * @var array
     */
    protected $map;

    /**
     * Sdk constructor.
     * @param CurlAdapter $adapter
     * @param string $token
     * @param EncoderInterface|null $encoder
     * @param string $customApiBaseUrl
     * @param bool $custom
     */
    public function __construct(
        CurlAdapter $adapter,
        string $token = '',
        ?EncoderInterface $encoder = null,
        string $customApiBaseUrl = '',
        bool $custom = false
    ) {
        $this->adapter = $adapter;
        $this->encoder = $encoder;
        $this->map = self::getMap($customApiBaseUrl, $custom);

        if ($token) {
            $this->adapter->appendHeaders(['X-Token: ' . $token]);
        }
    }

    /**
     * @param string $mode
     * @param bool $custom
     * @return array
     */
    protected function getMap(string $customBaseUrl, bool $custom): array
    {
        $baseUrl = 'https://eshop.catapulto.ru';
        if (!empty($customBaseUrl)) $baseUrl = $customBaseUrl;

        $arMap = [
            'cargo' => [
                'API' => $baseUrl . '/cargo',
                'REQUEST_TYPE' => 'POST',
            ],
            'companyIcon' => [
                'API' => $baseUrl . '/company-icon',
                'REQUEST_TYPE' => 'GET',
            ],
            'geo' => [
                'API' => $baseUrl . '/geo',
                'REQUEST_TYPE' => 'GET',
            ],
            'geoSearchFias' => [
                'API' => $baseUrl . '/geo/search-fias',
                'REQUEST_TYPE' => 'GET',
            ],
            'contact' => [
                'API' => $baseUrl . '/contact',
                'REQUEST_TYPE' => 'POST',
            ],
            'contactId' => [
                'API' => $baseUrl . '/contact/', // implements {id} to url
                'REQUEST_TYPE' => 'PUT',
            ],
            'rate' => [
                'API' => $baseUrl . '/rate',
                'REQUEST_TYPE' => 'POST',
            ],
            'rateId' => [
                'API' => $baseUrl . '/rate/', // implements {id} to url
                'REQUEST_TYPE' => 'GET',
            ],
            'tariffId' => [
                'API' => $baseUrl . '/tariff/', // implements {id} to url
                'REQUEST_TYPE' => 'GET',
            ],
            'shipment' => [
                'API' => $baseUrl . '/shipment',
                'REQUEST_TYPE' => 'POST',
            ],
            'shipmentId' => [
                'API' => $baseUrl . '/shipment/', // implements {id} to url
                'REQUEST_TYPE' => 'GET',
            ],
            'shipmentNpData' => [
                'API' => $baseUrl . '/shipment/np-data',
                'REQUEST_TYPE' => 'POST',
            ],
            'shipmentGoodsData' => [
                'API' => $baseUrl . '/shipment/goods-data',
                'REQUEST_TYPE' => 'POST',
            ],
            'terminal' => [
                'API' => $baseUrl . '/terminal',
                'REQUEST_TYPE' => 'GET',
            ],
            'terminalId' => [
                'API' => $baseUrl . '/terminal/', // implements {id} to url
                'REQUEST_TYPE' => 'GET',
            ],
            'CancelOrder' => [
                'API' => $baseUrl . '/shipment/reject/',
                'REQUEST_TYPE' => 'POST',
            ],
            'GetWSToken' => [
                'API' => $baseUrl . '/socket-settings',
                'REQUEST_TYPE' => 'GET',
            ],
        ];

        if (defined('IPOL_CATAPULTO_CUSTOM_MAP') && is_array(IPOL_CATAPULTO_CUSTOM_MAP)) {
            foreach (IPOL_CATAPULTO_CUSTOM_MAP as $method => $url) {
                $arMap[$method]['CUSTOM'] = $url;
            }
        }

        $arReturn = array();
        foreach ($arMap as $method => $arData) {
            if ($custom && isset($arData['CUSTOM'])) {
                $url = $arData['CUSTOM'];
            } else {
                $url = $arData['API'];
            }

            $arReturn[$method] = array(
                'URL' => $url,
                'REQUEST_TYPE' => $arData['REQUEST_TYPE']
            );
        }
        return $arReturn;
    }

    /**
     * @param string $method name of method in api-map
     */
    protected function configureRequest(string $method): void
    {
        if (array_key_exists($method, $this->map)) {
            $url = $this->map[$method]['URL'];
            $type = $this->map[$method]['REQUEST_TYPE'];
        } else {
            throw new Error('Requested method "' . $method . '" not found in module map!');
        }

        $this->adapter->setMethod($method);
        $this->adapter->setUrl($url);
        $this->adapter->setRequestType($type);
    }

    /**
     * @param Entity\Request\Cargo $data
     * @return Methods\Cargo
     * @throws BadResponseException
     */
    public function cargo(Entity\Request\Cargo $data): Methods\Cargo
    {
        $this->configureRequest(__FUNCTION__);
        return new Methods\Cargo($data, $this->adapter, $this->encoder);
    }

    /**
     * @return Methods\CompanyIcon
     * @throws BadResponseException
     */
    public function companyIcon(): Methods\CompanyIcon
    {
        $this->configureRequest(__FUNCTION__);
        return new Methods\CompanyIcon($this->adapter, $this->encoder);
    }

    /**
     * @param Entity\Request\Geo $data
     * @return Methods\Geo
     * @throws BadResponseException
     */
    public function geo(Entity\Request\Geo $data): Methods\Geo
    {
        $this->configureRequest(__FUNCTION__);
        return new Methods\Geo($data, $this->adapter, $this->encoder);
    }
    
    /**
     * @param Entity\Request\GeoSearchFias $data
     * @return Methods\GeoSearchFias
     * @throws BadResponseException
     */
    public function geoSearchFias(Entity\Request\GeoSearchFias $data): Methods\GeoSearchFias
    {
        $this->configureRequest(__FUNCTION__);
        return new Methods\GeoSearchFias($data, $this->adapter, $this->encoder);
    }

    /**
     * @param Entity\Request\Contact $data
     * @return Methods\Contact
     * @throws BadResponseException
     */
    public function contact(Entity\Request\Contact $data): Methods\Contact
    {
        $this->configureRequest(__FUNCTION__);
        return new Methods\Contact($data, $this->adapter, $this->encoder);
    }

    /**
     * @param int $id
     * @param Entity\Request\ContactId $data
     * @return Methods\ContactId
     * @throws BadResponseException
     */
    public function contactId(Entity\Request\ContactId $data): Methods\ContactId
    {
        $this->configureRequest(__FUNCTION__);
        return new Methods\ContactId($data, $this->adapter, $this->encoder);
    }

    /**
     * @param Entity\Request\Rate $data
     * @return Methods\Rate
     * @throws BadResponseException
     */
    public function rate(Entity\Request\Rate $data): Methods\Rate
    {
        $this->configureRequest(__FUNCTION__);
        return new Methods\Rate($data, $this->adapter, $this->encoder);
    }

    /**
     * @param string $id;
     * @param Entity\Request\RateId $data
     * @return Methods\RateId
     * @throws BadResponseException
     */
    public function rateId(Entity\Request\RateId $data): Methods\RateId
    {
        $this->configureRequest(__FUNCTION__);
        return new Methods\RateId($data, $this->adapter, $this->encoder);
    }

    /**
     * @param int $id;
     * @param Entity\Request\TariffId $data
     * @return Methods\TariffId
     * @throws BadResponseException
     */
    public function tariffId(Entity\Request\TariffId $data): Methods\TariffId
    {
        $this->configureRequest(__FUNCTION__);
        return new Methods\TariffId($data, $this->adapter, $this->encoder);
    }

    /**
     * @param Entity\Request\Shipment $data
     * @return Methods\Shipment
     * @throws BadResponseException
     */
    public function shipment(Entity\Request\Shipment $data): Methods\Shipment
    {
        $this->configureRequest(__FUNCTION__);
        return new Methods\Shipment($data, $this->adapter, $this->encoder);
    }

    /**
     * @param Entity\Request\ShipmentId $data
     * @return Methods\ShipmentId
     * @throws BadResponseException
     */
    public function shipmentId(Entity\Request\ShipmentId $data): Methods\ShipmentId
    {
        $this->configureRequest(__FUNCTION__);
        return new Methods\ShipmentId($data, $this->adapter, $this->encoder);
    }

    /**
     * @param Entity\Request\ShipmentGoodsData $data
     * @return Methods\ShipmentGoodsData
     * @throws BadResponseException
     */
    public function shipmentGoodsData(Entity\Request\ShipmentGoodsData $data): Methods\ShipmentGoodsData
    {
        $this->configureRequest(__FUNCTION__);
        return new Methods\ShipmentGoodsData($data, $this->adapter, $this->encoder);
    }

    /**
     * @param Entity\Request\ShipmentNpData $data
     * @return Methods\ShipmentNpData
     * @throws BadResponseException
     */
    public function shipmentNpData(Entity\Request\ShipmentNpData $data): Methods\ShipmentNpData
    {
        $this->configureRequest(__FUNCTION__);
        return new Methods\ShipmentNpData($data, $this->adapter, $this->encoder);
    }

    /**
     * @param Entity\Request\Terminal $data
     * @return Methods\Terminal
     * @throws BadResponseException
     */
    public function terminal(Entity\Request\Terminal $data): Methods\Terminal
    {
        $this->configureRequest(__FUNCTION__);
        return new Methods\Terminal($data, $this->adapter, $this->encoder);
    }

    /**
     * @param Entity\Request\TerminalId $data
     * @return Methods\TerminalId
     * @throws BadResponseException
     */
    public function terminalId(Entity\Request\TerminalId $data): Methods\TerminalId
    {
        $this->configureRequest(__FUNCTION__);
        return new Methods\TerminalId($data, $this->adapter, $this->encoder);
    }

    /**
     * @param Entity\Request\CancelOrder $data
     * @return Methods\CancelOrder
     * @throws BadResponseException
     */
    public function CancelOrder(Entity\Request\CancelOrder $data): Methods\CancelOrder
    {
        $this->configureRequest(__FUNCTION__);
        return new Methods\CancelOrder($data, $this->adapter, $this->encoder);
    }

    /**
     * @return Methods\GetWSToken
     * @throws BadResponseException
     */
    public function GetWSToken() {
        $this->configureRequest(__FUNCTION__);
        return new Methods\GetWSToken($this->adapter, $this->encoder);
    }

}
