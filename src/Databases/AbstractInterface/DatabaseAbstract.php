<?php

namespace EasySwoole\Migrate\Databases;

use EasySwoole\Migrate\Databases\Database\Mysql;
use EasySwoole\Spl\SplArray;

abstract class Database
{
    /** @var SplArray */
    protected $config = null;

    protected $databases = [];

    public function setConfig(SplArray $config) {
        $this->config = $config;
    }

    public function query(string $query){}
}