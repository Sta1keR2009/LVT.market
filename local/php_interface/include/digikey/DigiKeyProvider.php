<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

class DigiKeyProvider extends AbstractProvider
{
    use BearerAuthorizationTrait;

    protected $isSandbox = false;

    public function __construct(array $options = [], array $collaborators = [])
    {
        parent::__construct($options, $collaborators);
        $this->isSandbox = $options['isSandbox'] ?? false;
    }

    public function getBaseAuthorizationUrl()
    {
        return 'https://sso.digikey.com/as/authorization.oauth2';
    }

    public function getBaseAccessTokenUrl(array $params)
    {
        return 'https://sso.digikey.com/as/token.oauth2';
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return '';
    }

    protected function getDefaultScopes()
    {
        return ['api'];
    }

    protected function checkResponse(ResponseInterface $response, $data)
    {
        if (!empty($data['error'])) {
            $error = $data['error'];
            $code = $this->fetchResponseCode($response);
            throw new IdentityProviderException($error, $code, $data);
        }
    }

    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return null;
    }

    protected function getAuthorizationParameters(array $options)
    {
        $params = parent::getAuthorizationParameters($options);
        $params['redirect_uri'] = $this->redirectUri;
        return $params;
    }

    protected function fetchResponseCode(ResponseInterface $response)
    {
        return $response->getStatusCode();
    }

    public function getApiBaseUrl()
    {
        return $this->isSandbox 
            ? 'https://api.digikey.com' 
            : 'https://api.digikey.com';
    }
}