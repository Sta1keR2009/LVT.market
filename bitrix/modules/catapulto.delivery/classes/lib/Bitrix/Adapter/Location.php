<?php
namespace Ipol\Catapulto\Bitrix\Adapter;

use \Ipol\Catapulto\Bitrix\Handler\Locations;
use \Ipol\Catapulto\Core\Delivery\Location as CoreLocation;
use Ipol\Catapulto\Bitrix\Tools;

IncludeModuleLangFile(dirname(dirname(dirname(dirname(__DIR__)))) . '/include.php');

/**
 * Class Location
 * @package namespace Ipol\Catapulto\Bitrix\Adapter
 */
class Location
{
    private $bxId   = false;
    private $bxCode = false;

    /**
     * @var \Ipol\Catapulto\Core\Delivery\Location
     */
    private $coreLocation = null;



    public function __construct($possiblyId)
    {
        $location = Locations::getByBitrixId($possiblyId);

        if (!empty($location))
        {
            $this->bxId = $location['ID'];
            $this->bxCode = $location['CODE'];

            // optimize request for dadata
            if ($location['AREA'] == $location['NAME']) {
                $location['AREA'] = null;
            }

            // TODO: Add Area type to core loc and change this stuff
            $chain = implode(', ', array_filter([$location['COUNTRY'], $location['REGION'], $location['AREA'], $location['NAME']]));

            $this->coreLocation = new CoreLocation('cms');
            $this->coreLocation->setId($this->bxId)
                ->setCode($this->bxCode) // Can we use $possiblyId there ?
                ->setName($location['NAME'])
                ->setField('line', $chain)
                ->setField('type', $location['TYPE_CODE'])
                //->setName($chain)
                ->setCountry($location['COUNTRY'])
                ->setRegion($location['REGION'])
                ->setParent($location['PARENT_ID'])
            ;
        }
    }

    /**
     * @return \Ipol\Catapulto\Core\Delivery\Location
     */
    public function getCoreLocation()
    {
        return $this->coreLocation;
    }

    /**
     * @return mixed
     */
    public function getBxId()
    {
        return $this->bxId;
    }

    /**
     * @return mixed
     */
    public function getBxCode()
    {
        return $this->bxCode;
    }

    /** normalize settlement name for dadata
     * @param $settlement string settlement name
     *
     * @return string
     */
    public static function getNormalizeSettlement($settlement) {

        $arRemove = [
            Tools::getMessage('LOCTYPE_01'),
            Tools::getMessage('LOCTYPE_02'),
            Tools::getMessage('LOCTYPE_03'),
            Tools::getMessage('LOCTYPE_04'),
            Tools::getMessage('LOCTYPE_05'),
            Tools::getMessage('LOCTYPE_06'),
            Tools::getMessage('LOCTYPE_07'),
            Tools::getMessage('LOCTYPE_08'),
            Tools::getMessage('LOCTYPE_09'),
            Tools::getMessage('LOCTYPE_10'),
            Tools::getMessage('LOCTYPE_11'),
            Tools::getMessage('LOCTYPE_12'),
            Tools::getMessage('LOCTYPE_13'),
            Tools::getMessage('LOCTYPE_14'),
        ];

        foreach ($arRemove as $item) {
            $settlement = str_replace($item, '', $settlement);
        }

        return trim($settlement);
    }
}
