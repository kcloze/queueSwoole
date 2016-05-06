<?php
namespace Ycf\Model;

use Ycf\Core\YcfCore;

class ModelRedis
{
    private $_redis = null;
    public function __construct()
    {
        $this->_redis = YcfCore::load('_redis');
    }

    public function testRedis()
    {
        $this->_redis->sadd('test1', 1);
        $this->_redis->sadd('test1', 2);
        $this->_redis->sadd('test1', 3);
        $this->_redis->sadd('test2', 2);
        $this->_redis->sdiffstore('test3', array('test1', 'test2'));
        var_dump($this->_redis->smembers('test3'));
        // Use Redis commands
        $this->_redis->set('test', '7');
        var_dump($this->_redis->get('test'));
    }

}
