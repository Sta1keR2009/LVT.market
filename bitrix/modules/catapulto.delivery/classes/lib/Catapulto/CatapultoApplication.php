<?php


namespace Ipol\Catapulto\Catapulto;


use DateTime;
use Exception;
use Ipol\Catapulto\Api\Adapter\CurlAdapter;
use Ipol\Catapulto\Api\Client\CurlClient;
use Ipol\Catapulto\Api\Entity\EncoderInterface;
use Ipol\Catapulto\Api\Logger\Psr\Log\LoggerInterface;
use Ipol\Catapulto\Api\Sdk;
use Ipol\Catapulto\Catapulto\Controller\RequestCancelOrder;
use Ipol\Catapulto\Catapulto\Controller\RequestCargoCreate;
use Ipol\Catapulto\Catapulto\Controller\RequestCargoCreateByItem;
use Ipol\Catapulto\Catapulto\Controller\RequestCompanyIcon;
use Ipol\Catapulto\Catapulto\Controller\RequestContactCreate;
use Ipol\Catapulto\Catapulto\Controller\RequestContactUpdate;
use Ipol\Catapulto\Catapulto\Controller\RequestGeo;
use Ipol\Catapulto\Catapulto\Controller\RequestGeoSearchFias;
use Ipol\Catapulto\Catapulto\Controller\RequestGetWSToken;
use Ipol\Catapulto\Catapulto\Controller\RequestRateCreate;
use Ipol\Catapulto\Catapulto\Controller\RequestRateRead;
use Ipol\Catapulto\Catapulto\Controller\RequestShipmentCreate;
use Ipol\Catapulto\Catapulto\Controller\RequestShipmentGoodsCreate;
use Ipol\Catapulto\Catapulto\Controller\RequestShipmentNpCreate;
use Ipol\Catapulto\Catapulto\Controller\RequestShipmentRead;
use Ipol\Catapulto\Catapulto\Controller\RequestTariffRead;
use Ipol\Catapulto\Catapulto\Controller\RequestTerminalList;
use Ipol\Catapulto\Catapulto\Controller\RequestTerminalRead;
use Ipol\Catapulto\Core\Delivery\Shipment;
use Ipol\Catapulto\Core\Entity\CacheInterface;
use Ipol\Catapulto\Core\Order\Item;
use Ipol\Catapulto\Core\Order\Order;
use Ipol\Catapulto\Catapulto\Controller\AutomatedCommonRequest;
use Ipol\Catapulto\Catapulto\Controller\RequestController;


/**
 * Class CatapultoApplication
 * @package Ipol\Catapulto\Catapulto
 */
class CatapultoApplication extends GeneralApplication
{

    /**
     * @var string Auth token
     */
    protected $token = "";
    /**
     * @var bool
     * Indicates if the method was already called (for recurrent calls for dead jwt)
     */
    protected $recursionFlag = false;

    protected $curlClientType = CurlClient::class;

    /**
     * CatapultoApplication constructor.
     * @param string $token
     * @param string $customApiBaseUrl
     * @param int $timeout
     * @param EncoderInterface|null $encoder
     * @param CacheInterface|null $cache
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        string $token,
        string $customApiBaseUrl = '',
        int $timeout = 15,
        ?EncoderInterface $encoder = null,
        ?CacheInterface $cache = null,
        ?LoggerInterface $logger = null,
        ?string $curlClientType = null
    ) {
        $this->setToken($token)
            ->setCustomApiBaseUrl($customApiBaseUrl)
            ->setTimeout($timeout)
            ->setEncoder($encoder)
            ->setCache($cache)
            ->setLogger($logger);

        $this->abyss = [];
        $this->errorCollection = new ExceptionCollection();

        if (!empty($curlClientType)) $this->curlClientType = $curlClientType;

    }

    /**
     * @param AutomatedCommonRequest|mixed $controller
     * @param bool $useCache
     * @param int $cacheTTL
     * @return Entity\AbstractResult|mixed
     */
    private function genericCall($controller, bool $useCache = false, int $cacheTTL = 3600)
    {
        $resultObj = $controller->getResultObject();
        $this->setHash($controller->getSelfHash());
        if ($this->checkAbyss()) {
            $this->lastRequestType = 'abyss';
            return $this->abyss[$this->getHash()];
        } else {
            if ($useCache && $this->getCache() && $this->getCache()->setLife($cacheTTL)->checkCache($this->getHash())) {
                $this->lastRequestType = 'cache';
                return $this->getCache()->getCache($this->getHash());
            } else {
                $this->lastRequestType = 'direct';

                try {
                    $this->configureController($controller);
                } catch (Exception $e) {
                    $this->addError($e);
                    return $resultObj;
                }
                $controller->convert()
                    ->execute();

                if ($resultObj->getError()) {
                    if (($resultObj->getError()->getCode() == 401) && !$this->recursionFlag) {
                        $this->recursionFlag = true; //blocking further recursive calls
                        $this->addError($resultObj->getError());
                    }
                } else {
                    $this->toAbyss($resultObj);
                    if ($useCache) {
                        $this->toCache($resultObj, $this->getHash());
                    }
                }
            }
        }
        return $resultObj;
    }


    /*-----------------------------------------------------------------------*/

    /**
     * Get companies icons
     * @return Entity\CompanyIconResult
     */
    public function companyIcon(): Entity\CompanyIconResult
    {
        $controller = new RequestCompanyIcon(new Entity\CompanyIconResult());
        $controller->setSdkMethodName(__FUNCTION__);
        return $this->genericCall($controller);
    }

    /**
     * @param string|int $term City name or zip code
     * @param string $iso Lang code
     * @param int $limit Limit
     *
     * @return Entity\GeoResult
     */
    public function geo(
        $term,
        string $cityName = '',
        string $iso = 'ru',
        int $limit = 50,
        ?string $cityFiasId = null,
        ?string $settlementFiasId = null,
        ?string $fiasLevel = null,
        ?string $settlementType = null
    ): Entity\GeoResult
    {
        $controller = new RequestGeo(new Entity\GeoResult(), $term, $cityName, $iso, $limit, $cityFiasId, $settlementFiasId, $fiasLevel, $settlementType);
        $controller->setSdkMethodName(__FUNCTION__);
        return $this->genericCall($controller);
    }
    
    public function geoSearchFias(
        string $localityName,
        string $localityType,
        string $regionName = ''
    ): Entity\GeoSearchFiasResult
    {
        $controller = new RequestGeoSearchFias(new Entity\GeoSearchFiasResult(), $localityName, $localityType, $regionName);
        $controller->setSdkMethodName(__FUNCTION__);
        return $this->genericCall($controller);
    }

    /**
     * Contact create
     *
     * @param Order $cOrder
     *
     * @return Entity\ContactCreateResult
     */
    public function contactCreate(Order $cOrder): Entity\ContactCreateResult
    {
        $controller = new RequestContactCreate(new Entity\ContactCreateResult(), $cOrder);
        $controller->setSdkMethodName('contact');
        return $this->genericCall($controller);
    }

    /**
     * Contact update
     *
     * @param int $id
     * @param Order $cOrder
     *
     * @return Entity\ContactUpdateResult
     */
    public function contactUpdate(int $id, Order $cOrder): Entity\ContactUpdateResult
    {
        $controller = new RequestContactUpdate(new Entity\ContactUpdateResult(), $id, $cOrder);
        $controller->setSdkMethodName('contactId');
        return $this->genericCall($controller);
    }

    /**
     * @deprecated
     * Cargo create
     *
     * @param Order $cOrder
     *
     * @return Entity\CargoCreateResult
     */
    public function cargoCreate(Order $cOrder): Entity\CargoCreateResult
    {
        $controller = new RequestCargoCreate(new Entity\CargoCreateResult(), $cOrder);
        $controller->setSdkMethodName('cargo');
        return $this->genericCall($controller);
    }

    /**
     * @param Item $item
     * @return Entity\CargoCreateResult
     */
    public function cargoCreateByItem(Item $item): Entity\CargoCreateResult
    {
        $controller = new RequestCargoCreateByItem(new Entity\CargoCreateResult(), $item);
        $controller->setSdkMethodName('cargo');
        return $this->genericCall($controller);
    }

    /**
     * Rate create
     *
     * @param Order $cOrder
     *
     * @return Entity\RateCreateResult
     */
    public function rateCreate(Order $cOrder): Entity\RateCreateResult
    {
        $controller = new RequestRateCreate(new Entity\RateCreateResult(), $cOrder);
        $controller->setSdkMethodName('rate');
        return $this->genericCall($controller);
    }


    /**
     * Rate read
     *
     * @param string $id
     * @param int $pickup_days_shift
     * @param array $shipping_type_filter ['d2d', 'd2w', 'w2d', 'w2w']
     * @param array $services_filter ['NP','COD', 'etc...']
     * @param bool $need_insurance
     * @param float $insured_value float insurance cost
     *
     * @return Entity\RateReadResult
     */
    public function rateRead(
        string $id,
        int $pickup_days_shift = 0,
        array $shipping_type_filter = [],
        array $services_filter = [],
        bool $need_insurance = false,
        float $insured_value = 0
    ): Entity\RateReadResult
    {
        $controller = new RequestRateRead(
            new Entity\RateReadResult(),
            $id,
            $pickup_days_shift,
            $shipping_type_filter,
            $services_filter,
            $need_insurance,
            $insured_value
        );
        $controller->setSdkMethodName('rateId');
        return $this->genericCall($controller);
    }

    /**
     * Tariff read
     *
     * @param string $id
     * @param int $pickup_days_shift
     *
     * @return Entity\TariffReadResult
     */
    public function tariffRead(int $id, int $pickup_days_shift = 0): Entity\TariffReadResult
    {
        $controller = new RequestTariffRead(new Entity\TariffReadResult(), $id, $pickup_days_shift);
        $controller->setSdkMethodName('tariffId');
        return $this->genericCall($controller);
    }


    /**
     * Shipment create
     *
     * @param Order $cOrder
     *
     * @return Entity\ShipmentCreateResult
     */
    public function shipmentCreate(Order $cOrder): Entity\ShipmentCreateResult
    {
        $controller = new RequestShipmentCreate(new Entity\ShipmentCreateResult(), $cOrder);
        $controller->setSdkMethodName('shipment');
        return $this->genericCall($controller);
    }

    /**
     * Shipment read
     *
     * @param string $id
     *
     * @return Entity\ShipmentReadResult
     */
    public function shipmentRead(string $id): Entity\ShipmentReadResult
    {
        $controller = new RequestShipmentRead(new Entity\ShipmentReadResult(), $id);
        $controller->setSdkMethodName('shipmentId');
        return $this->genericCall($controller);
    }

    /**
     * ShipmentNp Create (������� ���������� ������)
     *
     * @param Order $cOrder
     *
     * @return Entity\ShipmentNpCreateResult
     */
    public function shipmentNpCreate(Order $cOrder): Entity\ShipmentNpCreateResult
    {
        $controller = new RequestShipmentNpCreate(new Entity\ShipmentNpCreateResult(), $cOrder);
        $controller->setSdkMethodName('shipmentNpData');
        return $this->genericCall($controller);
    }

    public function shipmentGoodsCreate(Order $cOrder): Entity\ShipmentGoodsCreateResult
    {
        $controller = new RequestShipmentGoodsCreate(new Entity\ShipmentGoodsCreateResult(), $cOrder);
        $controller->setSdkMethodName('shipmentGoodsData');
        return $this->genericCall($controller);
    }

    /**
     * Terminal List
     *
     * @param Order $cOrder
     *
     * @return Entity\TerminalListResult
     */
    public function terminalList(Order $cOrder): Entity\TerminalListResult
    {
        $controller = new RequestTerminalList(new Entity\TerminalListResult(),$cOrder);
        $controller->setSdkMethodName('terminal');
        return $this->genericCall($controller);
    }

    /**
     * Terminal read
     *
     * @param int $id
     *
     * @return Entity\TerminalReadResult
     */
    public function terminalRead(int $id): Entity\TerminalReadResult
    {
        $controller = new RequestTerminalRead(new Entity\TerminalReadResult(), $id);
        $controller->setSdkMethodName('terminalId');
        return $this->genericCall($controller);
    }

    public function cancelOrder(string $catapultoOrderId)
    {
        $controller = new RequestCancelOrder( new Entity\CancelOrderResult(), $catapultoOrderId );
        $controller->setSdkMethodName('CancelOrder');
        return $this->genericCall($controller);
    }

    public function getWSToken()
    {
        $controller = new RequestGetWSToken(new Entity\GetWSTokenResult());
        $controller->setSdkMethodName('GetWSToken');
        return $this->genericCall($controller);
    }

    /*-----------------------------------------------------------------------*/

    /**
     * @param RequestController $controller
     * sets sdk
     * @throws Exception
     */
    protected function configureController($controller)
    {
        $controller->setSdk($this->getSdk());
    }

    /**
     * @return Sdk
     * get the sdk-controller
     * ! timeout sets only here: later it wouldn't be changed !
     * @throws Exception
     */
    public function getSdk(): Sdk
    {
        $adapter = new CurlAdapter($this->getTimeout(), $this->curlClientType);
        if ($this->getLogger()) {
            $adapter->setLog($this->getLogger());
        }

        return new Sdk($adapter, $this->getToken(), $this->getEncoder(), $this->customApiBaseUrl, $this->customAllowed);
    }


    /**
     * @return string
     * @throws AppLevelException
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @param string $token
     *
     * @return CatapultoApplication
     */
    public function setToken(string $token): CatapultoApplication
    {
        $this->token = $token;
        return $this;
    }

}
