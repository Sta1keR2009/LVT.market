<?php

class ProxyConfig 
{
    public static function getProxies()
    {
        return [
            'switzerland_http' => [
                'proxy' => 'http://LM8Efc73:7cvVyzHi@50.114.175.184:64806',
                'country' => 'Switzerland',
                'type' => 'HTTP'
            ],
            'switzerland_socks5' => [
                'proxy' => 'socks5://LM8Efc73:7cvVyzHi@50.114.175.184:64807', 
                'country' => 'Switzerland',
                'type' => 'SOCKS5'
            ],
            'germany_http' => [
                'proxy' => 'http://LM8Efc73:7cvVyzHi@154.194.108.3:64612',
                'country' => 'Germany',
                'type' => 'HTTP'
            ],
            'germany_socks5' => [
                'proxy' => 'socks5://LM8Efc73:7cvVyzHi@154.194.108.3:64613',
                'country' => 'Germany', 
                'type' => 'SOCKS5'
            ],
            'czech_http' => [
                'proxy' => 'http://LM8Efc73:7cvVyzHi@156.233.40.235:64868',
                'country' => 'Czech Republic',
                'type' => 'HTTP'
            ],
            'spain_http' => [
                'proxy' => 'http://LM8Efc73:7cvVyzHi@154.209.159.131:62988',
                'country' => 'Spain',
                'type' => 'HTTP'
            ]
        ];
    }
}