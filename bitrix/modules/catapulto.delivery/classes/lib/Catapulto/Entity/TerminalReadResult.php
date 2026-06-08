<?php

namespace Ipol\Catapulto\Catapulto\Entity;

use Ipol\Catapulto\Api\Entity\Response\TerminalId as ResponseObj;
use Ipol\Catapulto\Api\Entity\Response\ErrorResponse;

/**
 * Class TerminalReadResult
 * @package Ipol\Catapulto\Application
 * @subpackage Result
 * @method ResponseObj|ErrorResponse getResponse()
 */
class TerminalReadResult extends AbstractResult
{

    /**
     * Method can be used in non-abstract Result objects,
     * to manipulate field values after successfully receiving response
     */
    public function parseFields(): void
    {

        if ($this->getResponse() && $item = $this->getResponse()->getData()->getFirst()) {

            $services = str_replace('\'','"',$item->getServices()); // Thanks for this commas, Catapulto-colleges :/

            if ($arServices = json_decode($services,true)) {
                $this->getResponse()->getData()->getFirst()->setServices($arServices);
            }
        }

        parent::parseFields();
    }



}
