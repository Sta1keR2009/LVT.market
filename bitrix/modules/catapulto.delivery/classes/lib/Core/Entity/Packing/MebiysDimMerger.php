<?php


namespace Ipol\Catapulto\Core\Entity\Packing;


/**
 * Class MebiysDimMerger
 * @package Ipol\Catapulto\Core
 * @subpackage Packing
 */
class MebiysDimMerger implements DimensionsMerger
{
    /**
     * @param $arGoods array(array(size1,size2,size3,quantity))
     * @return array of type ('L' => <length>, 'W' => <width>, 'H' => <height>)
     */
    public static function getSumDimensions($arGoods)
    {

        if(!is_array($arGoods) || !count($arGoods))
            return array('L'=>0,'W'=>0,'H'=>0);

        $arWork = array();
        foreach($arGoods as $good)
            $arWork []= self::sumSizeOneGoods($good[0], $good[1], $good[2], $good[3]);

        return self::sumSize($arWork);
    }

    protected static function sumSizeOneGoods($xi, $yi, $zi, $qty)
    {
        $ar = array($xi, $yi, $zi);
        sort($ar);
        if ($qty<=1) return (array('X'=>$ar[0],'Y'=>$ar[1],'Z'=>$ar[2]));

        $x1 = 0;
        $y1 = 0;
        $z1 = 0;
        $l = 0;

        $max1 = floor(Sqrt($qty));
        for($y=1;$y<=$max1;$y++){
            $i = ceil($qty/$y);
            $max2 = floor(Sqrt($i));
            for($z=1;$z<=$max2;$z++){
                $x = ceil($i/$z);
                $l2 = $x*$ar[0] + $y*$ar[1] + $z*$ar[2];
                if(($l==0)||($l2<$l)){
                    $l = $l2;
                    $x1 = $x;
                    $y1 = $y;
                    $z1 = $z;
                }
            }
        }
        return (array('X'=>$x1*$ar[0],'Y'=>$y1*$ar[1],'Z'=>$z1*$ar[2]));
    }

    protected static function sumSize($a)
    {
        $n = count($a);
        if (!($n>0)) return(array('L'=>'0','W'=>'0','H'=>'0'));
        for($i3=1;$i3<$n;$i3++){
            // sort sizes big to small
            for($i2=$i3-1;$i2<$n;$i2++){
                for($i=0;$i<=1;$i++){
                    if($a[$i2]['X']<$a[$i2]['Y']){
                        $a1 = $a[$i2]['X'];
                        $a[$i2]['X'] = $a[$i2]['Y'];
                        $a[$i2]['Y'] = $a1;
                    }
                    if(($i==0) && ($a[$i2]['Y']<$a[$i2]['Z'])){
                        $a1 = $a[$i2]['Y'];
                        $a[$i2]['Y'] = $a[$i2]['Z'];
                        $a[$i2]['Z'] = $a1;
                    }
                }
                $a[$i2]['Sum'] = $a[$i2]['X'] + $a[$i2]['Y'] + $a[$i2]['Z']; // sum of sides
            }
            // sort cargo from small to big
            for($i2=$i3;$i2<$n;$i2++)
                for($i=$i3;$i<$n;$i++)
                    if($a[$i-1]['Sum']>$a[$i]['Sum']){
                        $a2 = $a[$i];
                        $a[$i] = $a[$i-1];
                        $a[$i-1] = $a2;
                    }
            // calculate sum dimensions of two smallest cargoes
            if($a[$i3-1]['X']>$a[$i3]['X']) $a[$i3]['X'] = $a[$i3-1]['X'];
            if($a[$i3-1]['Y']>$a[$i3]['Y']) $a[$i3]['Y'] = $a[$i3-1]['Y'];
            $a[$i3]['Z'] = $a[$i3]['Z'] + $a[$i3-1]['Z'];
            $a[$i3]['Sum'] = $a[$i3]['X'] + $a[$i3]['Y'] + $a[$i3]['Z']; // sum of sides
        }

        $a = array(
            Round($a[$n-1]['X'],2),
            Round($a[$n-1]['Y'],2),
            Round($a[$n-1]['Z'],2)
        );
        rsort($a);

        return array(
            'L' => $a[0],
            'W' => $a[1],
            'H' => $a[2]
        );
    }

}