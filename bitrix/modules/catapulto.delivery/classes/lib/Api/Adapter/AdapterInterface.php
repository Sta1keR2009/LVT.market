<?php
namespace Ipol\Catapulto\Api\Adapter;

interface AdapterInterface
{
    public function post(string $method, array $dataPost = []);
}