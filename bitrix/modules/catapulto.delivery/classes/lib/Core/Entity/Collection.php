<?php


namespace Ipol\Catapulto\Core\Entity;


/**
 * Class Collection
 * @package Ipol\Catapulto\Core
 * @subpackage Entity
 * Object for storing uniform data, that replaces arrays
 * Accessors must declare property, which name will be transfer to parent constructor - that will be container
 * of array with content
 */
class Collection
{
    /**
     * @var int
     */
    protected $index;

    /**
     * @var string
     */
    protected $error;

    /**
     * @var string - name of container for all collection elements
     */
    protected $field;

    /**
     * Collection constructor.
     * @param $field
     */
    public function __construct($field)
    {
        $this->field = $field;

        $this->$field = array();

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
     * @return string
     */
    public function getField(string $code)
    {
        return $this->field;
    }

    /**
     * @return string
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
        if(array_key_exists($index, $this->{$this->field})){
            array_splice($this->{$this->field}, $index, $index);
            sort($this->{$this->field});
            $this->reset();
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * @return $this
     * clears everything
     */
    public function clear()
    {
        if(property_exists($this, $this->field)){
            $this->{$this->field} = [];
        }

        $this->reset();

        return $this;
    }

    /**
     * @param string $error
     * @param bool $clear
     * @return $this
     */
    public function setError($error, $clear = false)
    {
        $this->error = ($this->error && !$clear) ? $this->error.", ".$error : $error;

        return $this;
    }

    /**
     * @param $something - new element that will be added to collection
     * @return $this
     */
    public function add($something)
    {
        array_push($this->{$this->field}, $something);

        return $this;
    }

    /**
     * @return mixed|null - returns next element of collection
     */
    public function getNext()
    {
        if(count($this->{$this->field}) < ($this->index) + 1)
            return null;

        return $this->{$this->field}[$this->index++];
    }

    /**
     * @return int - be aware: indexes start from 0, so last index equals getQuantity() - 1
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * @return mixed|null - returns first element of collection
     */
    public function getFirst()
    {
        if(!count($this->{$this->field}))
            return null;

        return $this->{$this->field}[0];
    }

    /**
     * @return mixed|null
     */
    public function getLast()
    {
        if(!$counter = count($this->{$this->field}))
            return null;

        return $this->{$this->field}[$counter - 1];
    }

    /**
     * @return int - be aware: indexes start from 0, so last index equals getQuantity() - 1
     */
    public function getQuantity()
    {
        return count($this->{$this->field});
    }
}
