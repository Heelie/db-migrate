<?php

namespace EasySwoole\Migrate\Databases;

use EasySwoole\Spl\SplArray;

interface DatabaseInterface
{
    public function connect(SplArray $config);

    public function query(string $query);

    public function close();
}