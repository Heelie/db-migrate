<?php

namespace EasySwoole\Migrate\Databases\AbstractInterface;

use EasySwoole\Spl\SplArray;
use RuntimeException;

/**
 * Class DatabaseAbstract
 * @package EasySwoole\Migrate\Databases\AbstractInterface
 * @author heelie.hj@gmail.com
 * @date 2020/8/22 21:26:20
 */
abstract class DatabaseAbstract
{
    /** @var SplArray */
    protected $config = null;

    protected $databases = [];

    public function getConfig()
    {
        if (is_null($this->config)) {
            // temporary...
            $devConfig = require EASYSWOOLE_ROOT . '/dev.php';
            if (!isset($devConfig['DATABASE'])) {
                throw new RuntimeException('Database configuration information was not read');
            }
            $this->setConfig(new SplArray($devConfig['DATABASE']));
        }
        return $this->config;
    }

    public function setConfig(SplArray $config)
    {
        $this->config = $config;
    }

    abstract public function query(string $query);
}