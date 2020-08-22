<?php

namespace EasySwoole\Migrate\Databases\Database;

use EasySwoole\Migrate\Databases\AbstractInterface\DatabaseInterface;
use EasySwoole\Spl\SplArray;
use mysqli;
use RuntimeException;

/**
 * Class Mysql
 * @package EasySwoole\Migrate\Databases\Database
 * @author heelie.hj@gmail.com
 * @date 2020/8/22 21:21:35
 */
class Mysql implements DatabaseInterface
{
    /** @var mysqli */
    private $resource;

    public function connect(SplArray $config)
    {
        $this->resource = new mysqli($config->host, $config->username, $config->password, $config->dbname, $config->port ?: 3306);
        if ($this->resource->connect_error) {
            throw new RuntimeException('database connect error:' . $this->resource->connect_error);
        }
        $this->resource->query('SET NAMES UTF8');
        return $this;
    }

    public function query(string $query)
    {
        $result = $this->resource->query($query);
        if (is_bool($result)) {
            if ($result === false && $this->resource->error) {
                throw new RuntimeException($this->resource->error . PHP_EOL . ' SQL:' . $query);
            }
            return $result;
        } else {
            return $result->fetch_all(MYSQLI_ASSOC);
        }
    }

    public function close()
    {
        $this->resource->close();
    }
}