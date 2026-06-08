<?php

namespace Ipol\Catapulto\Others;

class Collection
{
    protected $index;

    protected $error;

    protected $field;

    public function __construct($field)
    {
        $this->field = $field;

        //if(property_exists($this,$this->field)){
        $this->$field = array();
        //}


        $this->reset();

        return $this;
    }

    /**
     * @return $this
     * resets index of array
     */
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
     * @param $index
     * @return bool
     * deletes element, if not exists - returns false, otherwise - resets index and returns true
     */
    public function delete($index)
    {
        $link = $this->field;

        if(array_key_exists($index,$this->$link))
        {
            array_splice($this->$link, $index, $index);
            sort($this->$link);
            $this->reset();
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return $this
     * clears everything
     */
    public function clear()
    {
        $link = $this->field;

        if(property_exists($this,$link))
        {
            $this->$link = array();
        }

        $this->reset();

        return $this;
    }

    /**
     * @param mixed $error
     * @param bool $clear
     * @return $this
     */
    public function setError($error,$clear=false)
    {
        $this->error = ($this->error && !$clear) ? $this->error.", ".$error : $error;

        return $this;
    }

    // abstract public function getNext();

    public function add($something)
    {
        $link = $this->field;

        array_push($this->$link,$something);

        return $this;
    }

    public function getNext()
    {
        $link = $this->field;

        if(count($this->$link) < ($this->index) +1)
            return false;

        $arValues = $this->$link;

        return $arValues[$this->index++];
    }

    public function getIndex(){
        return $this->index-1;
    }

    public function getFirst()
    {
        $link = $this->field;

        if(!count($this->$link))
            return false;

        $arValues = $this->$link;

        return $arValues[0];
    }
}