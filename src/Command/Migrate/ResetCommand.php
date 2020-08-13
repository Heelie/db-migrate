<?php

namespace EasySwoole\Migrate\Command\Migrate;

use EasySwoole\Command\AbstractInterface\CommandHelpInterface;
use EasySwoole\Command\AbstractInterface\CommandInterface;
use EasySwoole\Command\AbstractInterface\ResultInterface;
use EasySwoole\Command\Color;
use EasySwoole\Command\CommandManager;
use EasySwoole\Migrate\Command\MigrateCommand;
use EasySwoole\Migrate\Databases\DatabaseFacade;
use EasySwoole\Migrate\Utility\Util;
use Exception;

final class ResetCommand extends MigrateCommand implements CommandInterface
{
    private $dbFacade;

    public function __construct()
    {
        $this->dbFacade = DatabaseFacade::getInstance();
    }

    public function commandName(): string
    {
        return 'migrate reset';
    }

    public function desc(): string
    {
        return 'database migrate reset';
    }

    public function help(CommandHelpInterface $commandHelp): CommandHelpInterface
    {
        return $commandHelp;
    }

    /**
     * @return ResultInterface|string|null
     * @throws Exception
     */
    public function exec(): ?string
    {
        $waitRollbackFiles = $this->getRollbackFiles();

        $outMsg = [];

        foreach ($waitRollbackFiles as $id => $file) {
            $outMsg[] = "<brown>Migrating: </brown>{$file}";
            $startTime = microtime(true);
            $className = Util::migrateFileNameToClassName($file);
            try {
                $ref = new \ReflectionClass($className);
                $sql = call_user_func([$ref->newInstance(), 'down']);
                if ($this->dbFacade->query($sql)) {
                    $deleteSql = "delete from `" . Util::DEFAULT_MIGRATE_TABLE . "` where `id`='{$id}' ";
                    $this->dbFacade->query($deleteSql);
                }
            } catch (\Throwable $e) {
                return Color::error($e->getMessage());
            }
            $outMsg[] = "<green>Migrated:  </green>{$file} (" . round(microtime(true) - $startTime, 2) . " seconds)";
        }
        $outMsg[] = "<success>Migration table rollback successfully.</success>";
        return Color::render(implode(PHP_EOL, $outMsg));
    }

    private function getRollbackFiles()
    {
        $tableName = Util::DEFAULT_MIGRATE_TABLE;
        $sql = "select `id`,`migration` from `{$tableName}` order by `id` desc ";
        $readyRollbackFiles = $this->dbFacade->query($sql);
        if (empty($readyRollbackFiles)) {
            return Color::success('No files to be rollback.');
        }
        $readyRollbackFiles = array_column($readyRollbackFiles, 'migration', 'id');

        foreach ($readyRollbackFiles as $id => $file) {
            $file = Util::MIGRATE_PATH . $file . ".php";
            if (file_exists($file)) {
                Util::requireOnce($file);
            }
        }
        return $readyRollbackFiles;
    }

}
