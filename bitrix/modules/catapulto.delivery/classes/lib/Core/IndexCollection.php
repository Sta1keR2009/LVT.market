<?php

namespace Ipol\Catapulto\Core;

use Exception;
use Ipol\Catapulto\Core\Entity\Collection;

/**
 * Class IndexCollection
 * @package Ipol\Catapulto\Core
 * Collection, modified for indexed object search, here index is some field, stored in element,
 * which can be accessed through method passed in constructor
 */
class IndexCollection extends Collection
{
    /**
     * @var string - field in collected elements corresponds to array 4 quick select
     */
    protected $linkMethod;

    /**
     * Keeps links index => elementLink
     * @var array
     */
    protected $links;

    /**
     * @throws Exception
     */
    public function __construct($field, $linkMethod)
    {
        if(!$linkMethod){
            throw new Exception('No link for indexing');
        }

        $this->linkMethod  = $linkMethod;
        $this->fleeLinks();

        parent::__construct($field);
    }

    /**
     * totally clears links
     */
    protected function fleeLinks()
    {
        $this->links = array();
    }

    public function clear()
    {
        $this->fleeLinks();
        return parent::clear();
    }

    public function delete($index)
    {
        if(parent::delete($index)){
            $this->fleeLinks();
            $link = $this->field;
            foreach ($this->$link as $key => $element){
                if(method_exists($element, $this->linkMethod)){
                    $method = $this->linkMethod;
                    $this->addLink($key, $element->$method());
                    return true;
                } else {
                    $this->setError('Unable to call method '.($this->linkMethod).' in given object');
                    return false;
                }
            }
        }

        return false;
    }

    public function deleteByLink($link){
        $index = $this->getIndexByLink($link);
        if($index !== false){
            return $this->delete($index);
        }

        return false;
    }

    public function add($something)
    {
        if(method_exists($something, $this->linkMethod)){
            parent::add($something);
            $link = $this->field;
            $index = count($this->$link);
            $method = $this->linkMethod;
            $this->addLink($index-1, $something->$method());
        } else {
            $this->setError('Unable to call method '.($this->linkMethod).' in given object');
        }

        return $this;
    }

    /**
     * Returns element by link
     * @param $link
     * @return mixed
     */
    public function getByLink($link)
    {
        $index = $this->getIndexByLink($link);
        if($index !== false){
            $container = $this->field;
            $container = $this->$container;
            return $container[$index];
        }

        return false;
    }

    protected function getIndexByLink($link)
    {
        if(array_key_exists($link, $this->links)){
            $index = $this->links[$link];
            $container = $this->field;
            if(array_key_exists($index, $this->$container)){
                return $index;
            }
        }

        return false;
    }

    /**
     * Adds link => index
     * @param $index
     * @param $link
     */
    protected function addLink($index, $link)
    {
        $this->links[$link] = $index;
    }
}