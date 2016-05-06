<?php

namespace Ycf\Core;

/**
 * 系统助手类
 *
 *
 */
class YcfUtils
{

    /**
     * 获得来源类型 post get
     *
     * @return unknown
     */
    public static function method()
    {
        return strtoupper(isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET');
    }
    /**
     * [exit 兼容swoole运行环境]
     * @param  [type] $msg [description]
     * @return [type]      [description]
     */
    public static function exitMsg($msg)
    {
        if (!defined('SWOOLE')) {
            exit($msg);
        } else {
            throw new Swoole\ExitException($msg);
        }
    }
}
