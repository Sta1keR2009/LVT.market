<?php


namespace Ipol\Catapulto\Core\Entity;


/**
 * Interface CacheInterface
 * @package Ipol\Catapulto\Core
 * @subpackage Entity
 */
interface CacheInterface
{
    /**
     * @param $life
     * @return mixed
     * Sets duration on cache's existence
     */
    public function setLife($life);

    /**
     * @param $hash
     * @return mixed
     * receives data from cache
     */
    public function getCache($hash);

    /**
     * @param $hash
     * @param $data
     * @return mixed
     * puts data in cache with hash-key
     */
    public function setCache($hash, $data);

    /**
     * @param $hash
     * @return mixed
     * checks existence of cache
     */
    public function checkCache($hash);
}