<?php


namespace Ipol\Catapulto\Core\Entity\Packing;


use Ipol\Catapulto\Core\Entity\Packing\Arrangement\Box;

/**
 * Class ViaDimMerger
 * @package Ipol\Catapulto\Core
 * @subpackage Packing
 */
class ViaDimMerger implements DimensionsMerger
{
    /**
     * @param $arGoods array(array(size1,size2,size3,quantity))
     * @return array of type ('L' => <length>, 'W' => <width>, 'H' => <height>)
     */
    public static function getSumDimensions($arGoods)
    {

        if(!is_array($arGoods) || !count($arGoods))
            return array('L'=>0,'W'=>0,'H'=>0);

        $arBoxes = array();
        foreach($arGoods as $good)
        {
            $tmp_box1 = new Box();
            $tmp_box1->setLength($good[0])
                ->setWidth($good[1])
                ->setHeight($good[2])
                ->rotateToNorm();
            $arBoxes[] = $tmp_box1;
        }
        $arBoxes = array_unique($arBoxes, SORT_REGULAR);
        usort($arBoxes, ['self', 'dimBoxSorter']);

        $resDims = ['L' => $arBoxes[0]->getLength(), 'W' => $arBoxes[0]->getWidth(), 'H' => $arBoxes[0]->getHeight()];
        foreach ($arBoxes as $box)
        {
            if($box->getWidth() > $resDims['W'])
                $resDims['W'] = $box->getWidth();
            if($box->getHeight() > $resDims['H'])
                $resDims['H'] = $box->getHeight();
        }

        return $resDims;
    }

    /**
     * @param Box $box1
     * @param Box $box2
     * @return int
     */
    protected static function dimBoxSorter($box1, $box2)
    {
        if($box1->getLength() != $box2->getLength())
            return ($box1->getLength() < $box2->getLength())? 1 : -1;
        else if($box1->getWidth() != $box2->getWidth())
            return ($box1->getWidth() < $box2->getWidth())? 1 : -1;
        else if($box1->getHeight() != $box2->getHeight())
            return ($box1->getHeight() < $box2->getHeight())? 1 : -1;
        else
            return 0;
    }

}