<?php
/*
 *异步mysql模型类-示例
 */
namespace Ycf\Model;

class ModelProxyDb
{
    //protected $client = null;

    //从mysql连接池中拿数据
    public static function query($sql)
    {
        $client = new \swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_SYNC);
        $client->connect('127.0.0.1', 9509, 0.5, 0);
        $client->send($sql);
        return $client->recv();
    }

}
