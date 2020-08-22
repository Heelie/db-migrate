<?php

namespace EasySwoole\Migrate\Databases;

use EasySwoole\Migrate\Databases\AbstractInterface\DatabaseAbstract;
use EasySwoole\Migrate\Databases\AbstractInterface\DatabaseInterface;
use EasySwoole\Migrate\Databases\Database\Mysql;
use EasySwoole\Spl\SplArray;
use ReflectionClass;
use RuntimeException;
use Throwable;

/**
 * Database Facade
 * Class DatabaseFacade
 * @package EasySwoole\Migrate\Databases
 * @author heelie.hj@gmail.com
 * @date 2020/06/30 15:56:21
 */
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
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function check()
    {
        /** get default database type */
        $default = $this->getConfig()->get('default');
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
                $ref = new ReflectionClass($this->databases[$default]);
                static::$database = $ref->newInstance();
            } catch (Throwable $e) {
                throw new RuntimeException($e->getMessage());
            }
        }
        static::$database->connect(new SplArray($this->config->get($default)));
        return call_user_func([static::$database, __FUNCTION__], $query);
    }
}