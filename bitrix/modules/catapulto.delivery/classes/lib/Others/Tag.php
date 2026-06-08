<?php

namespace Ipol\Catapulto\Others;

use Ipol\Catapulto\Others\collection;

/**
 * Class tag
 * @package Ipol\Catapulto\Others
 * штука для обработки тегов. Мне было скучно.
 * Атрибуты всегда пишутся как атрибут = 'что-то' - одинарные кавычки будут заэкранированы
 */
class Tag
{
    protected $name;
    protected $class;
    /**
     * @var array of type 'attrName => val'
     */
    protected $attrs;
    protected $text;
    /**
     * @var Collection
     */
    protected $content;

    protected $doBrake = false;

    public function __construct($name)
    {
        $this->content = new Collection('tags');

        $this->name = $name;

        $this->attrs = array();
        $this->class = array();

        return $this;
    }

    // HTML

    /**
     * @param bool $reverse по умолчанию сначала идет контент - потом текст (если есть), установка в true - наоборот
     */
    public function placeHTML($reverse = false)
    {
        ?>
        <<?=$this->getName()?> <?=$this->getClassPrint()?> <?=$this->getAttrsPrint()?>><?if($reverse) {$this->pasteText($reverse);}?><?$this->placeContent()?><?if(!$reverse){$this->pasteText($reverse);}?></<?=$this->getName()?>>
        <?=($this->isDoBrake() ? '<br>' : '')?>
        <?
    }

    public function placeContent()
    {
        if($this->getContent()->getFirst()){
            $this->getContent()->reset();

            /** @var Tag $obContent */
            while($obContent = $this->getContent()->getNext())
            {
                $obContent->placeHTML();
            }
        }
    }

    protected function pasteText($reverse = false)
    {
        if($this->getText()){
            echo $this->getText();
            if($reverse && $this->getContent()->getFirst())
            {
                echo '<br>';
            }
        }
    }

    /**
     * @return string
     * return string 4 attrs
     */
    protected function getAttrsPrint()
    {
        $strAttr = '';
        foreach ($this->attrs as $attrName => $attrVal)
        {
            $strAttr .= $attrName."='" .$attrVal. "'";
        }

        return $strAttr;
    }

    /**
     * @return string
     * returns string 4 classes
     */
    protected function getClassPrint(){
        $strClass = '';
        if(!empty($this->class)){
            $strClass = "class='".implode(' ',$this->class)."'";
        }

        return $strClass;
    }

    // WORKOUT

    /**
     * @return array
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param string|array $class
     * return $this
     */
    public function setClass($class)
    {
        if(!is_array($class)){
            $class = array($class);
        }

        $this->class = $class;

        return $this;
    }

    /**
     * @param $class
     * @return $this
     */
    public function addClass($class)
    {
        if(!in_array($class,$this->class)){
            $this->class []= $class;
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAttrs()
    {
        return $this->attrs;
    }

    /**
     * @param array $attrs
     * return $this
     */
    public function setAttrs($attrs)
    {
        if(is_array($attrs)){
            foreach($attrs as $name => $val)
            {
                if($name == 'class')
                {
                    $this->addClass($val);
                } else {
                    $attrs[$name] = self::attrVal($val);
                }
            }
            $this->attrs = $attrs;
        } else {
            $this->attrs = array();
        }

        return $this;
    }

    /**
     * @param $attr
     * @param $value
     * @return $this
     */
    public function addAttr($attr,$value)
    {
        if($attr == 'class')
        {
            $this->addClass($value);
        } else {
            $this->attrs[$attr] = self::attrVal($value);
        }

        return $this;
    }

    protected static function attrVal($value)
    {
        return str_replace("'","\'",(string)$value);
    }

    /**
     * @return Collection
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param Collection $content
     * return $this
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @param $obTag
     * @return $this
     * adds tag to content
     */
    public function addTag($obTag)
    {
        $this->getContent()->add($obTag);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param mixed $text
     * @return $this
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }


    /**
     * @return bool
     */
    public function isDoBrake()
    {
        return $this->doBrake;
    }

    /**
     * @param bool $doBrake
     * @return $this
     */
    public function setDoBrake($doBrake)
    {
        $this->doBrake = $doBrake;

        return $this;
    }
}