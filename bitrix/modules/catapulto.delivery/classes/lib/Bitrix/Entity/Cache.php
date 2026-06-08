<?php

namespace Ipol\Catapulto\Bitrix\Entity;

use Ipol\Catapulto\Core\Entity\CacheInterface;

/**
 * Class cache
 * @package Ipol\Catapulto
 * Класс для работы с кэшем
 */
class Cache extends \CPHPCache implements CacheInterface
{
    /**
     * @var int
     * Время жизни кэша
     */
    protected $life;

    /**
     * @var string
     * Путь к файлам кэша, по факту можно юзать лейбл модуля
     */
    protected $path;

    /**
     * @var bool
     * Был ли инициализирован (чтобы checkCache каждый раз не делать)
     */
    protected $inited = false;

    public function __construct()
    {
        parent::__construct();

        $this->path = '/'.\Ipol\Catapulto\AbstractGeneral::getMODULELBL().'CACHE/';

        $this->life = 86400;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param mixed $path
     * @return $this
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @param $hash
     * @return bool
     *
     */
    public function checkCache($hash)
    {
		if(
			!(defined(self::getDeactCacheConst()) && constant(self::getDeactCacheConst()) === true) && //если есть переменная для отключения кэша - проверяем тут
			$this->InitCache($this->getLife(),$hash,$this->getPath())
		)
		{
			$this->inited = true;
			return true;
		}
		return false;
    }

    public function getCache($hash)
    {
        //if(!$this->inited)
            $this->checkCache($hash);

        return $this->GetVars();
    }

    public function setCache($hash, $data)
    {
        //if(!$this->inited)
            $this->checkCache($hash);

        $this->StartDataCache();
        $this->EndDataCache($data);
    }

    /**
     * @return int
     */
    public function getLife()
    {
        return $this->life;
    }

    /**
     * @param int $life
     * @return $this
     */
    public function setLife($life)
    {
        $this->life = intval($life);

        return $this;
    }

    /**
     * @return string
     * Получаем название константы, которая отключает кэш
     */
    public static function getDeactCacheConst()
    {
        return \Ipol\Catapulto\AbstractGeneral::getMODULELBL().'NOCACHE';
    }
}