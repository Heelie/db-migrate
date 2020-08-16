<?php

namespace EasySwoole\Migrate\Databases\AbstractInterface;

use EasySwoole\Spl\SplArray;

interface DatabaseInterface
{
    public function connect(SplArray $config);

    public function query(string $query);

    public function close();
}