<?php
namespace Ipol\Catapulto\Api\Entity;


use Ipol\Catapulto\Api\BadResponseException;
use Ipol\Catapulto\Api\Tools;

/**
 * У наследника должно быть объявлено поле, которое передается в родитель как $field - по нему идет коллекционирование
 * Class AbstractCollection
 * @package Ipol\Catapulto\Api\Entity
 */
abstract class AbstractCollection
{
    protected $index;

    protected $error;

    protected $field;

    protected $childClass;

    public function __construct($field)
    {
        $this->field = $field;
        $this->setChildClass();
        if (property_exists($this, $this->field)) {
            $this->$field = array();
        }

        $this->reset();
    }

    public function reset()
    {
        $this->index = 0;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @return mixed
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @param mixed $error
     * @param bool $clear
     * @return $this
     */
    public function setError($error, $clear = false)
    {
        $this->error = ($this->error && !$clear) ? $this->error . ", " . $error : $error;

        return $this;
    }

    public function add($something)
    {
        $link = $this->field;

        array_push($this->$link, $something);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getNext()
    {
        $link = $this->field;

        if (count($this->$link) < ($this->index) + 1) {
            return false;
        }

        $arValues = $this->$link;

        return $arValues[$this->index++];
    }


    /**
     * @return mixed
     */
    public function getFirst()
    {
        $link = $this->field;

        if (!count($this->$link)) {
            return false;
        }

        $arValues = $this->$link;

        return $arValues[0];
    }

    public function getLast()
    {
        $link = $this->field;

        if(!$counter = count($this->$link))
            return false;

        $arValues = $this->$link;

        return $arValues[$counter - 1];
    }

    /**
     * @return int
     */
    public function getQuantity(): int
    {
        $link = $this->field;
        return count($this->$link);
    }


    public function getFields()
    {
        $fields = [];
        $this->reset();
        while ($pack = $this->getNext()) {
            /* @var AbstractEntity $pack */
            $fields[] = $pack->getFields();
        }
        $this->reset();

        return $fields;
    }

    /**
     * @return mixed
     */
    public function getChildClass()
    {
        return $this->childClass;
    }

    /**
     * @param string $directSet
     * @return AbstractCollection
     */
    public function setChildClass(string $directSet = '')
    {
        if (!$directSet) {
            $child_namespace = get_class($this);
            $pos = strrpos($child_namespace, '\\');
            $child_namespace = substr($child_namespace, 0, $pos + 1);
            $this->childClass = ($child_namespace . substr($this->getField(), 0, -1));
        } else {
            $this->childClass = $directSet;
        }
        return $this;
    }

    /**
     * @param $list array|\stdClass[]
     * @return $this
     * @throws BadResponseException
     */
    public function fillFromArray(array $list)
    {
        if (Tools::isSeqArr($list)) {
            $childClass = $this->getChildClass();
            foreach ($list as $item) {
                $adding = new $childClass();
                if (is_object($item)) {
                    /** @var $item \stdClass */
                    $adding->setFields($item);
                }
                $this->add($adding);
            }
            return $this;
        } else {
            throw new BadResponseException(__FUNCTION__ . ' requires parameter to be SEQUENTIAL array. ' . gettype($list) . ' given.');
        }
    }

}