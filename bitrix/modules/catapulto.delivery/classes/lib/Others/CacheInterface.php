<?php
namespace Ipol\Catapulto\Others;


interface CacheInterface
{
    /**
     * @param $life
     * @return mixed
     * Sets duration on cache's existance
     */
    public function setLife($life);

    /**
     * @param $hash
     * @return mixed
     * recieves data from cache
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
     * checks existance of cache
     */
    public function checkCache($hash);
}