<?php

namespace EasySwoole\Migrate\Command;

use EasySwoole\Command\AbstractInterface\ResultInterface;

abstract class CommandInterface
{
    /**
     * @var string
     */
    public $commandName;

    /**
     * @param $args
     * @return ResultInterface|void
     */
    public function exec($args): ResultInterface{}
}
