<?php

namespace EasySwoole\Migrate\Command\AbstractInterface;

use EasySwoole\Command\AbstractInterface\CommandHelpInterface;
use EasySwoole\Command\AbstractInterface\CommandInterface;
use EasySwoole\Command\CommandManager;

abstract class CommandAbstract implements CommandInterface
{
    abstract public function commandName(): string;

    abstract public function exec(): ?string;

    abstract public function help(CommandHelpInterface $commandHelp): CommandHelpInterface;

    abstract public function desc(): string;

    protected function getArg($name, $default = null)
    {
        return CommandManager::getInstance()->getArg($name, $default);
    }

    protected function getOpt($name, $default = null)
    {
        return CommandManager::getInstance()->getOpt($name, $default);
    }
}
