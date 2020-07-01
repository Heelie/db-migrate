<?php

namespace EasySwoole\Migrate\Utility;

use EasySwoole\Command\AbstractInterface\ResultInterface;
use EasySwoole\Command\Result;

/**
 * Class Output
 * @package EasySwoole\Migrate\Utility
 */
class Output
{
    /**
     * @param $msg
     * @param null $helps
     * @return ResultInterface
     */
    static function outSucc($msg, $helps = null): ResultInterface
    {
        return self::outResult($msg, 'success', $helps);
    }

    /**
     * @param $msg
     * @param null $helps
     * @return ResultInterface
     */
    static function outError($msg, $helps = null): ResultInterface
    {
        return self::outResult($msg, 'error', $helps);
    }

    /**
     * @param $msg
     * @param string $status
     * @param null $helps
     * @return ResultInterface
     */
    private static function outResult($msg, $status = 'error', $helps = null): ResultInterface
    {
        if ($status === 'error') {
            $msg = "\33[41m\33[1;37m{$msg}\33[0m";
        } elseif ($status === 'success') {
            $msg = "\33[42m\33[1;37m{$msg}\33[0m";
        }
        $result = new Result();
        if ($helps) {
            if (is_array($helps)) {
                $msg .= PHP_EOL . implode(PHP_EOL, array_map(function ($help) {
                        return 'php easyswoole ' . $help;
                    }, $helps));
            } elseif (is_string($helps)) {
                $msg .= PHP_EOL . $helps;
            }
        }
        $result->setMsg($msg);
        return $result;
    }

    static function output($msg)
    {
        $result = new Result();
        $result->setMsg($msg);
        return $result;
    }
}