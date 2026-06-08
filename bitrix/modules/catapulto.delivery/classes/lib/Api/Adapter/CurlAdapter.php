<?php


namespace Ipol\Catapulto\Api\Adapter;


use Exception;
use Ipol\Catapulto\Api\ApiLevelException;
use Ipol\Catapulto\Api\BadResponseException;
use Ipol\Catapulto\Api\Client\CurlClient;
use Ipol\Catapulto\Api\Client\WPClient;

/**
 * Class CurlAdapter
 * @package Ipol\Catapulto\Api\Adapter
 */
class CurlAdapter extends AbstractAdapter
{
    /**
     * @var curl
     */
    protected $curl;
    /**
     * @var array
     */
    private $allowedCodeArr;
    /**
     * @var array
     */
    private $validErrorCodeArr;
    /**
     * @var string
     */
    protected $url;
    /**
     * @var string
     */
    protected $requestType;
    /**
     * @var array
     */
    protected $headers = [];
    /**
     * @var string
     */
    protected $contentType = 'Content-Type: application/json; charset=utf-8';
    /**
     * @var string
     */
    protected $method = 'unconfigured_request';

    /**
     * CurlAdapter constructor.
     * @param int $timeout
     * @throws Exception if curl not installed
     */
    public function __construct(int $timeout = 15, string $curlClientType = CurlClient::class)
    {
        parent::__construct();
        $this->allowedCodeArr = [200,201];
        $this->validErrorCodeArr = [400, 401, 403, 404, 409, 429, 500, 502];
        $this->curl = new $curlClientType(false, array(
            CURLOPT_TIMEOUT_MS     => $timeout * 1000,
        ));
    }

    /**
     * @param array $dataPost
     * @param string $urlImplement
     * @param array $dataGet
     * @return mixed
     * @throws ApiLevelException
     * @throws BadResponseException
     */
    public function form(array $dataPost = [], string $urlImplement = "", array $dataGet = [])
    {
        $this->curl->setOpt(CURLOPT_RETURNTRANSFER, TRUE);

        $getStr = (!empty($dataGet))? "?" . http_build_query($dataGet) : "";

        $this->curl->setUrl($this->getUrl() . $urlImplement . $getStr);

        $this->log->debug('', [
            'method' => $this->method,
            'process' => 'REQUEST',
            'content' => [
                'URL' => $this->curl->getUrl(),
                'DATA' => $dataPost,
                'FORM' => http_build_query($dataPost, JSON_UNESCAPED_UNICODE)
            ],
        ]);

        $this->applyHeaders()->curl->post(http_build_query($dataPost));

        $this->log->debug('', [
            'method' => $this->method,
            'process' => 'RESPONSE',
            'content' => [
                'CODE' => $this->curl->getCode(),
                'BODY' => $this->curl->getAnswer()
            ],
        ]);

        $this->afterCheck($dataPost);

        return $this->curl->getAnswer();
    }

    /**
     * @param array $dataPost
     * @param string $urlImplement
     * @param array $dataGet
     * @return mixed
     * @throws ApiLevelException
     * @throws BadResponseException
     */
    public function post(array $dataPost = [], string $urlImplement = "", array $dataGet = [])
    {
        $this->curl->setOpt(CURLOPT_RETURNTRANSFER, true);

        $getStr = (!empty($dataGet))? "?" . http_build_query($dataGet) : "";

        $this->curl->setUrl($this->getUrl() . $urlImplement . $getStr);

        $this->log->debug('', [
            'method' => $this->method,
            'process' => 'REQUEST',
            'content' => [
                'URL' => $this->curl->getUrl(),
                'DATA' => $dataPost,
                'JSON' => json_encode($dataPost, JSON_UNESCAPED_UNICODE)
            ],
        ]);

        $this->applyHeaders()->curl->post(json_encode($dataPost, JSON_UNESCAPED_UNICODE));

        $this->log->debug('', [
            'method' => $this->method,
            'process' => 'RESPONSE',
            'content' => [
                'CODE' => $this->curl->getCode(),
                'BODY' => $this->curl->getAnswer()
            ],
        ]);

        $this->afterCheck($dataPost);

        return $this->curl->getAnswer();
    }

    /**
     * @param string $urlImplement
     * @param array $dataGet
     * @return mixed
     * @throws ApiLevelException
     * @throws BadResponseException
     */
    public function get(string $urlImplement = "", array $dataGet = [])
    {
        $this->curl->setOpt(CURLOPT_RETURNTRANSFER, TRUE);

        $this->curl->setUrl($this->getUrl() . $urlImplement);

        $getStr = empty($dataGet) ? '' : '?' . http_build_query($dataGet); //only for logging, imitating inner curl.php process

        $this->log->debug('', [
            'method' => $this->method,
            'process' => 'REQUEST',
            'content' => ['URL' => $this->curl->getUrl() . $getStr],
        ]);

        $this->applyHeaders()->curl->get($dataGet);

        $this->log->debug('', [
            'method' => $this->method,
            'process' => 'RESPONSE',
            'content' => [
                'CODE' => $this->curl->getCode(),
                'BODY' => $this->curl->getAnswer()
            ],
        ]);

        $this->afterCheck('get request');

        return $this->curl->getAnswer();
    }

    /**
     * @param array $dataPut
     * @param string $urlImplement
     * @param array $dataGet
     * @return mixed
     * @throws ApiLevelException
     * @throws BadResponseException
     */
    public function put(array $dataPut = [], string $urlImplement = "", array $dataGet = [])
    {
        $this->curl->setOpt(CURLOPT_RETURNTRANSFER, TRUE);

        $getStr = (!empty($dataGet))? "?" . http_build_query($dataGet) : "";

        $this->curl->setUrl($this->getUrl() . $urlImplement . $getStr);

        $this->log->debug('', [
            'method' => $this->method,
            'process' => 'REQUEST',
            'content' => [
                'URL' => $this->curl->getUrl(),
                'DATA' => $dataPut,
                'JSON' => json_encode($dataPut, JSON_UNESCAPED_UNICODE)
            ],
        ]);

        $this->applyHeaders()->curl->put(json_encode($dataPut,JSON_UNESCAPED_UNICODE));

        $this->log->debug('', [
            'method' => $this->method,
            'process' => 'RESPONSE',
            'content' => [
                'CODE' => $this->curl->getCode(),
                'BODY' => $this->curl->getAnswer()
            ],
        ]);

        $this->afterCheck($dataPut);

        return $this->curl->getAnswer();
    }

    /**
     * @param string $urlImplement
     * @return mixed
     * @throws ApiLevelException
     * @throws BadResponseException
     */
    public function delete(string $urlImplement = "")
    {
        $this->applyHeaders()->curl->setUrl($this->getUrl() . $urlImplement);

        $this->log->debug('', ['method' => $this->method,
                'process' => 'REQUEST',
                'content' => [
                    'URL' => $this->curl->getUrl(),
                    ]
            ]);

        $this->curl->delete();

        $this->log->debug('', [
            'method' => $this->method,
            'process' => 'RESPONSE',
            'content' => [
                'CODE' => $this->curl->getCode(),
                'BODY' => $this->curl->getAnswer(),
            ],
        ]);

        $this->afterCheck('delete request');

        return $this->curl->getAnswer();
    }

    /**
     * @return string
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return CurlAdapter
     */
    public function setUrl(string $url): CurlAdapter
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRequestType(): ?string
    {
        return $this->requestType;
    }

    /**
     * @param string $requestType
     * @return CurlAdapter
     */
    public function setRequestType(string $requestType): CurlAdapter
    {
        $this->requestType = $requestType;
        return $this;
    }

    /**
     * @param string $contentType
     * @return $this
     */
    public function setContentType(string $contentType): CurlAdapter
    {
        $this->contentType = $contentType;
        return $this;
    }

    /**
     * @param array $headers
     * @return CurlAdapter
     */
    public function appendHeaders(array $headers): CurlAdapter
    {
        $this->headers = array_merge($this->headers, $headers);
        return $this;
    }

    /**
     * @return $this
     */
    protected function applyHeaders(): CurlAdapter
    {
        $this->appendHeaders([$this->contentType]);
        $this->curl->config([CURLOPT_HTTPHEADER => $this->headers]);
        return $this;

    }

    /**
     * @return curl
     */
    public function getCurl(): curl
    {
        return $this->curl;
    }

    /**
     * @param string $method
     * @return CurlAdapter
     */
    public function setMethod(string $method): CurlAdapter
    {
        $this->method = $method;
        return $this;
    }

    /**
     * @param $sentData
     * @throws ApiLevelException
     * @throws BadResponseException
     */
    protected function afterCheck($sentData): void
    {
        if ($this->curl->getCurlErrNum() == CURLE_OPERATION_TIMEDOUT) {
            throw new BadResponseException('Connection timed out', $this->curl->getCurlErrNum());
        }
        if (!in_array($this->curl->getCode(), $this->allowedCodeArr)) {
            if (in_array($this->curl->getCode(), $this->validErrorCodeArr)) {
                throw new ApiLevelException('Request error',
                    $this->curl->getCode(),
                    $this->curl->getUrl(),
                    $sentData,
                    $this->curl->getAnswer(),
                    $this->curl->getArrResponseHeaders());
            } else {
                throw new BadResponseException('Bad server answer: ' . $this->curl->getAnswer(),
                    $this->curl->getCode(),
                    $this->curl->getArrResponseHeaders()
                );
            }
        }
    }

}
