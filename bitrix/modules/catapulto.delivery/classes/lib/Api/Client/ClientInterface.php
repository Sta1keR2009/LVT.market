<?php


namespace Ipol\Catapulto\Api\Client;


interface ClientInterface
{

    public function __construct($url = false, array $config = []);

    public function get(array $data = []): self;

    public function post(string $data = ''): self;

    public function put(string $data = ''): self;

    public function delete(): self;

    public function config(array $args): self;

    public function setOpt(int $opt, $val): self;

    public function getCode(): ?int;

    public function getAnswer();

    //public function getRequest(): array;

    public function setUrl(string $url): self;

    public function getUrl(): string;

    public function getCurlErrNum(): int;

    public function getArrResponseHeaders(): array;
}
