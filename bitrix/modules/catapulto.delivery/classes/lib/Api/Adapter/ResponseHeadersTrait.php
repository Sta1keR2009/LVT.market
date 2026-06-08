<?php


namespace Ipol\Catapulto\Api\Adapter;


trait ResponseHeadersTrait
{
    protected $arHeaders = [];

    public function setHeaders(array $headers = []): void
    {
        $this->arHeaders = $headers;
    }

    /**
     * @param string $headerName
     * @param int $index - in some cases there can be more than one header with one name.
     * in that case we can parse them by index.
     * But for 99% cases we can suggest that index other as 0 will not give more info.
     * @return string
     */
    public function getHeader(string $headerName, $index = 0): string
    {
        if (array_key_exists($headerName, $this->arHeaders) &&
            array_key_exists($index, $this->arHeaders[$headerName])
        ) {
            return $this->arHeaders[$headerName][$index];
        } else {
            return '';
        }
    }
}