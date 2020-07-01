<?php

namespace EasySwoole\Migrate\Databases\Database;

use EasySwoole\Migrate\Databases\DatabaseInterface;
use EasySwoole\Spl\SplArray;
use mysqli;
use RuntimeException;

class Mysql implements DatabaseInterface
{
    /** @var mysqli */
    private $resource;

    public function connect(SplArray $config)
    {
        $this->resource = new mysqli($config->host, $config->username, $config->password, $config->dbname, $config->port);
        if ($this->resource->connect_error) {
            throw new RuntimeException('database connect error:' . $this->resource->connect_error);
        }
        $this->resource->query('SET NAMES UTF8');
        return $this;
    }

    public function query(string $query)
    {
        $result = $this->resource->query($query);
        // var_dump($result);
        // die;
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