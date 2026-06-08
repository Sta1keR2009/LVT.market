<?php

namespace Ipol\Catapulto\Bitrix\Controller;


use Ipol\Catapulto\Admin\BitrixLoggerController;
use Ipol\Catapulto\Api\Adapter\CurlAdapter;
use Ipol\Catapulto\Api\ApiLevelException;
use Ipol\Catapulto\Api\BadResponseException;
use Ipol\Catapulto\Option;

class Dadata extends AbstractController
{
    protected CurlAdapter $adapter;
    
    protected string $apikey;
    
    public const BASE_URL = "https://suggestions.dadata.ru/suggestions/api/4_1/rs/";
    
    public const SUGGESTION_COUNT = 5;
    
    public const TIMEOUT_SEC = 10;
    
    public const LOGGER_NAME = 'Catapulto_API';
    
    public function __construct()
    {
        parent::__construct(CATAPULTO_DELIVERY, CATAPULTO_DELIVERY_LBL);
        $this->adapter = new CurlAdapter(self::TIMEOUT_SEC);
        $this->apikey  = Option::get('dadataApikey');
        if (Option::get('debug') === 'Y') {
            $this->adapter->setLog(new BitrixLoggerController(self::LOGGER_NAME));
        }
    }
    
    /**
     * Ищет адреса по любой части адреса от региона до квартиры («самара авроры 7 12» → «443017, Самарская обл, г Самара, ул Авроры, д 7, кв 12»).
     * Также ищет по почтовому индексу («105568» → «г Москва, ул Магнитогорская»).
     * https://dadata.ru/api/suggest/address/
     *
     * @param $name
     * @param $query
     * @param int $count
     * @param array $dataParams
     *
     * @return false|mixed
     * @throws ApiLevelException
     * @throws BadResponseException
     */
    public function suggest($name, $query, int $count = self::SUGGESTION_COUNT, array $dataParams = [])
    {
        if (empty($this->apikey)) {
            return false;
        }
        
        $url  = "suggest/$name";
        $data = ["query" => $query, "count" => $count];
        $data += $dataParams;
        
        if ($this->encoder) {
            $data = $this->encoder->encodeToAPI($data);
        }
        
        $this->adapter->appendHeaders([
            'Authorization: Token ' . $this->apikey
        ]);
        $this->adapter->setUrl(self::BASE_URL . $url);
        
        $response = $this->adapter->post($data);
        if ($this->encoder) {
            $response = $this->encoder->encodeFromAPI($response);
        }
        if ($suggest = json_decode($response, true)) {
            return $suggest;
        }
        
        return false;
    }
    
    /**
     * Находит адрес по идентификатору:
     * кадастровый номер (stead_cadnum, house_cadnum или flat_cadnum), только для России;
     * ФИАС-код, он же ГАР-код (fias_id), только для России;
     * КЛАДР-код (kladr_id), только для России;
     * Идентификатор OpenStreetMap (fias_id), только для Беларуси, Казахстана и Узбекистана;
     * Идентификатор GeoNames (geoname_id), для всех остальных стран.
     * По КЛАДР-коду ищет до улицы, по ФИАС-коду — до квартиры.
     *
     * https://dadata.ru/api/find-address/
     *
     * @param       $name
     * @param       $query
     * @param int   $count
     * @param array $dataParams
     *
     * @return false|mixed
     * @throws ApiLevelException
     * @throws BadResponseException
     */
    public function findById($name, $query, int $count = self::SUGGESTION_COUNT, array $dataParams = [])
    {
        if (empty($this->apikey)) {
            return false;
        }
        
        $url  = "findById/$name";
        $data = ["query" => $query, "count" => $count];
        $data += $dataParams;
        
        if ($this->encoder) {
            $data = $this->encoder->encodeToAPI($data);
        }
        
        $this->adapter
            ->setUrl(self::BASE_URL . $url)
            ->appendHeaders([
                'Content-Type: application/json',
                'Accept: application/json',
                'Authorization: Token ' . $this->apikey
            ]);
        
        $response = $this->adapter->post($data);
        
        if ($this->encoder) {
            $response = $this->encoder->encodeFromAPI($response);
        }
        
        if ($suggest = json_decode($response, true)) {
            return $suggest;
        }
        
        return false;
    }
}
