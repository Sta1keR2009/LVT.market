<?php

namespace Ipol\Catapulto;


use Ipol\Catapulto\Bitrix\Entity\BasicResponse;
use Ipol\Catapulto\Bitrix\Entity\Encoder;
use Ipol\Catapulto\Bitrix\Entity\Options;
use Ipol\Catapulto\Bitrix\Tools;
use Ipol\Catapulto\Catapulto\CatapultoApplication;

class AuthHandler extends AbstractGeneral
{
    public static function auth($params)
    {
        OptionsHandler::clearCache(true);
        
        Option::set('isConnected', 'N');
        
        $workTry = self::authRequest($params);
        
        if ($workTry->isSuccess()) {
            self::login($params);
        }
        
        if (Tools::isModuleAjaxRequest()) {
            echo Tools::jsonEncode(
                [
                    'success' => $workTry->isSuccess(),
                    'error'   => $workTry->getErrorText(),
                ]
            );
        }
        
        return $workTry;
    }
    
    /**
     * @param      $params
     * @param bool $testMode
     *
     * @return BasicResponse
     * Checking auth via Application
     */
    public static function authRequest($params)
    {
        $encoder     = new Encoder();
        $Application = new CatapultoApplication($params['apikey'], $params['custom_url'], 6, $encoder);
        $Response    = new BasicResponse();
        
        try {
            //ѕровер€ем авторизацию на простом методе API катапульто, т.к. специального метода нет
            $companyIcon = $Application->companyIcon();
            
            if ($companyIcon->isSuccess()) {
                $Response->setSuccess(true);
            }
            else {
                
                $errorCollection = $Application->getErrorCollection();
                $errText         = '';
                while ($error = $errorCollection->getNext()) {
                    $errText .= $error->getMessage() . '; ';
                }
                
                $errText .= $companyIcon->getError()->getCode()."\n".
                            $companyIcon->getError()->getMessage()."\n";
                //$Response->setSuccess(false)->setErrorText(substr($errText, 0, strlen($errText) - 2));
                $Response->setSuccess(false)->setErrorText($errText);
            }
        } catch (\Exception $exception) {
            $Response->setSuccess(false);
            $Response->setErrorText($exception->getMessage());
        }
        
        return $Response;
    }
    
    
    /**
     * @param array $params
     * Login and making a lot of needful stuff
     */
    public static function login($params)
    {
        Option::set('apikey', $params['apikey']);
        Option::set('isConnected', 'Y');
        if ($params['custom_url'] == '') {
            Option::set('isTest', 'N');
            Option::set('customApiUrl','');
        } else {
            Option::set('isTest', 'Y');
            Option::set('customApiUrl',$params['custom_url']);
        }
        
        SubscribeHandler::register();
        
        AgentHandler::addAgent('refreshStatuses', 1800);
        AgentHandler::addAgent('syncServiceData', 86400);
    }
    
    /**
     * Deloginning - remove options, clear cache, kill subscribes, removing agents
     */
    public static function delogin()
    {
        Option::set('apikey', false);
        Option::set('isConnected', 'N');
        Option::set('isTest', 'N');
        Option::set('sync_data_completed',  'N');
        
        SubscribeHandler::unRegister();
        
        \CAgent::RemoveModuleAgents(self::$MODULE_ID);
        
        OptionsHandler::clearCache(true);
        
        if (Tools::isModuleAjaxRequest()) {
            echo 'Y';
        }
    }
    
    /**
     * @return bool
     */
    public static function isAuthorized()
    {
        $options = new Options();
        
        /**  Ipol\Catapulto\Bitrix\Entity::__call */
        return $options->fetchIsConnected() == 'Y';
    }
}