<?php
namespace Ipol\Catapulto\Bitrix\Controller;

//use \Ipol\Catapulto\Admin\Logger;
use Ipol\Catapulto\Admin\BitrixLoggerController;
use \Ipol\Catapulto\Bitrix\Entity\Cache;
use \Ipol\Catapulto\Bitrix\Entity\Encoder;
use \Ipol\Catapulto\Bitrix\Entity\Options;
use Ipol\Catapulto\Bitrix\Tools;
use Ipol\Catapulto\Catapulto\CatapultoApplication;

/**
 * Class AbstractController
 * @package Ipol\Catapulto\Bitrix\Controller
 * Parent class for all controllers. Provide fields with entities for all controllers
 */
class AbstractController
{
    /**
     * @var Options module options entity
     */
    protected $options;

    /**
     * @var Encoder for text encoding operations
     */
    protected $encoder;

    /**
     * @var string hash for cache
     */
    protected $hash;

    /**
     * @var Cache entity for cache
     */
    protected $cache;

    /**
     * @var BitrixLoggerController entity for logs
     */
    protected $logger;

    /**
     * @var string name for file with API-logs
     */
    protected $loggerName = 'Catapulto_API';

    /**
     * @var CatapultoApplication entity for API calls
     */
    protected $application;

    protected static $MODULE_ID;
    protected static $MODULE_LBL;

    public function __construct($module_id, $module_lbl)
    {
        self::$MODULE_ID  = $module_id;
        self::$MODULE_LBL = $module_lbl;

        $this->options = new Options();
        $this->encoder = new Encoder();

        $this->logger  = new BitrixLoggerController($this->loggerName);

        $this->cache   = new Cache();

        $customApiUrl = $this->getOptions()->fetchOption('customApiUrl');
        if ($this->getOptions()->fetchIsTest() != 'Y') $customApiUrl = '';

        $this->application = new CatapultoApplication(
            $this->getOptions()->fetchOption('apikey'),
            $customApiUrl,
            $this->getOptions()->fetchTimeout(),
            $this->encoder,
            $this->cache,
            $this->getLogger()
        );
    }

    /**
     * @return Options
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param Options $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }

    /**
     * @return BitrixLoggerController
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @return string
     */
    public function getLoggerName()
    {
        return $this->loggerName;
    }


}