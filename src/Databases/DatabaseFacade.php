<?php
/**
 * Database Facade
 * @Author:heelie.hj@gmail.com
 * @Date:2020/06/30 15:56:21
 */

namespace EasySwoole\Migrate\Databases;

use EasySwoole\Migrate\Databases\AbstractInterface\DatabaseAbstract;
use EasySwoole\Migrate\Databases\Database\Mysql;
use EasySwoole\Spl\SplArray;
use RuntimeException;
use Throwable;

class DatabaseFacade extends DatabaseAbstract
{
    private static $instance;

    /**
     * @var DatabaseInterface
     */
    private static $database;

    /** @var SplArray */
    protected $config = null;

    /**
     * @var string[]
     */
    protected $databases = [
        'mysql' => Mysql::class
    ];

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    /**
     * @param SplArray $config
     */
    public function setConfig(SplArray $config)
    {
        $this->config = $config;
    }

    private function check()
    {
        if (is_null($this->config)) {
            // temporary...
            $devConfig = require EASYSWOOLE_ROOT . '/dev.php';
            if (!isset($devConfig['DATABASE'])) {
                throw new RuntimeException('Database configuration information was not read');
            }
            $this->setConfig(new SplArray($devConfig['DATABASE']));
        }
        /** get default database type */
        $default = $this->config->get('default');
        if (!isset($this->databases[$default])) {
            throw new RuntimeException(sprintf('This database "%s" is not supported', $default));
        }
    }

    /**
     * @param string $query
     * @return mixed|void
     */
    public function query(string $query)
    {
        $this->check();
        $default = $this->config->get('default');
        if (!static::$database) {
            try {
                $ref = new \ReflectionClass($this->databases[$default]);
                /** @var DatabaseInterface $database instance */
                static::$database = $ref->newInstance();
            } catch (Throwable $e) {
                throw new RuntimeException($e->getMessage());
            }
        }
        static::$database->connect(new SplArray($this->config->get($default)));
        return call_user_func([static::$database, __FUNCTION__], $query);
    }
}