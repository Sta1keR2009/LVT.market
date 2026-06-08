<?php

class SimpleDigiKeyAuth
{
    private $clientId;
    private $clientSecret;
    private $redirectUri;

    public function __construct()
    {
        $this->clientId = 'uEodk824rK2UOfxvMynvIXnPlPMmlz9He6eDh4oQANnGUivF';
        $this->clientSecret = 'VVGFDAx4VCgOGTDBXgAA7LM0PNSUnustYao96CWqJSbDH1AUh5DFSU3vqtPdqKtr';
        $this->redirectUri = 'https://lvt.market/api/digikey/callback/index.php';
    }

    public function getAuthorizationUrl()
    {
        $params = [
            'client_id' => $this->clientId,
            'response_type' => 'code',
            'redirect_uri' => $this->redirectUri,
            'scope' => 'api'
        ];

        $url = 'https://sso.digikey.com/as/authorization.oauth2?' . http_build_query($params);
        
        return $url;
    }

    public function testUrl()
    {
        $url = $this->getAuthorizationUrl();
        
        echo "<h2>Generated URL:</h2>";
        echo "<textarea style='width: 100%; height: 100px;'>" . $url . "</textarea>";
        
        echo "<h2>URL Parts:</h2>";
        $parts = parse_url($url);
        echo "<pre>";
        print_r($parts);
        echo "</pre>";
        
        if (isset($parts['query'])) {
            parse_str($parts['query'], $query);
            echo "<h2>Query Parameters:</h2>";
            echo "<pre>";
            print_r($query);
            echo "</pre>";
        }
        
        echo "<br><a href='" . $url . "' style='font-size: 18px; padding: 10px; background: blue; color: white;'>🔗 TEST THIS URL</a>";
    }
}