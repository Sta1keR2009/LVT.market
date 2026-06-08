<?php

namespace Ipol\Catapulto\Api\Client;

class WPClient implements ClientInterface
{
    const BASE_CURL_HTTP_HEADERS = 10023; //curl base http headers const

    private $url = '';
    private $timeout = 15000;

    /**
     * @var array
     */
    protected $headers = [];

    private $getting_headers;
    private $data_request;
    private $answer;

    private $code;

    public function __construct($url = false, array $config = [])
    {
        if (gettype($url) == 'string' && !empty($url)) $this->url = $url;
    }

    // do nothing...
    public function put(string $data = ''): self
    {
        return $this;
    }

    // do nothing...
    public function delete(): self
    {
        return $this;
    }

    public function config(array $args): self
    {
        foreach ($args as $key => $value) {
            if ($key === self::BASE_CURL_HTTP_HEADERS) $this->headers = array_merge($this->headers,$value);
        }
        return $this;
    }

    public function setOpt(int $opt, $val): self
    {
        return $this;
    }

    public function getCode(): ?int
    {
        return $this->code;
    }

    public function getAnswer()
    {
        return $this->answer;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;
        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getCurlErrNum(): int
    {
        return 0;
    }

    public function get(array $data = []): self
    {
        if ($data) {
            if (strpos($this->url, '?') !== false) {
                $this->url = substr($this->url, 0, strpos($this->url, '?'));
            }
            $this->url .= '?' . http_build_query($data);
        }

        $this->wp_request('GET','');
        return $this;
    }

    public function post(string $data = ''): self
    {
        $this->wp_request('POST',$data);
        return $this;
    }

    public function getArrResponseHeaders(): array
    {
        return $this->getting_headers;
    }

    private function wp_request($method='POST',$reqdata='') {
        $headers=[]; //prepare headers
        foreach ($this->headers as $hdr) {
            $pair=explode(': ',$hdr,2);
            $headers[trim($pair[0])]=trim($pair[1]);
        }
        $return = wp_remote_request($this->url,[
            'method'=>$method,
            'headers'=>$headers,
            'cookies'=>[],
            'body'=>$reqdata,
        ]);

        if ($return instanceof \WP_Error) {
            $errstr = '';
            foreach ($return->get_error_messages() as $msg) $errstr.=$msg;
            $this->code = 500;
            $this->answer = $errstr;
            return;
        }

        $this->data_request = $return['response'];

        //collect headers
        $res_headers = $return['headers']->getAll();
        $this->getting_headers=$res_headers;

        $this->code = $this->data_request['code'];
        $this->answer=$return['body'];
    }
}
