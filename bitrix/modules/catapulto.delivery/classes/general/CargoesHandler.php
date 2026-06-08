<?php
namespace Ipol\Catapulto;

use Ipol\Catapulto\Bitrix\Tools;
use Ipol\Catapulto\Core\Delivery\Cargo;

class CargoesHandler extends AbstractGeneral
{

    /**
     * Count cargo dimensions for params given and echoes JSON answer
     * @param array $params
     * @return void
     */
    public static function countCargoDimensions($params)
    {
        $coreCargo = new Cargo();
        $coreCargo
            ->fromArray($params['cargo'])
            ->calculateDimensions(true);

        $result = array(
            "success" => true,
            "length"  => $coreCargo->getLength(),
            "width"   => $coreCargo->getWidth(),
            "height"  => $coreCargo->getHeight(),
        );

        echo Tools::jsonEncode($result);
    }

}
