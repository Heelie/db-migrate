<?php

namespace EasySwoole\Migrate\Databases\AbstractInterface;

use EasySwoole\Spl\SplArray;

abstract class DatabaseAbstract
{
    /** @var SplArray */
    protected $config = null;

    protected $databases = [];

    public function setConfig(SplArray $config) {
        $this->config = $config;
    }

    public function query(string $query){}
}