<?php

namespace EasySwoole\Migrate\Command\Migrate;

use EasySwoole\Command\AbstractInterface\ResultInterface;
use EasySwoole\Command\Result;
use EasySwoole\Migrate\Command\CommandInterface;

class RollbackCommand extends CommandInterface
{
    public function exec($args = []): ResultInterface
    {
        if (empty($args) || is_null($args)){

        }
        $result = new Result();
        $result->setMsg('install');
        return $result;
    }
}