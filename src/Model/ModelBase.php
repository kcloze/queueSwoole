<?php
namespace Ycf\Model;

use Ycf\Core\YcfCore;
use Ycf\Core\YcfDB;
use Ycf\Core\YcfRedis;

class ModelBase
{
    protected $_db    = null;
    protected $_redis = null;
    public function __construct()
    {
        $this->_db = $this->load('_db');
    }

    protected function load($obj)
    {
        switch ($obj) {
            case '_db':
                return $this->getDbInstance();
                break;
            case '_redis':
                return $this->getRedisInstance();
                break;
            default:
                break;
        }
    }

    protected function getDbInstance()
    {
        // Create Mysql Client instance with you configuration settings
        if (null == $this->_db) {
            $this->_db = new YcfDB(YcfCore::$_settings['Mysql']);
        }
        return $this->_db;
    }
    protected function getRedisInstance()
    {
        if (!extension_loaded('redis')) {
            throw new \RuntimeException('php redis extension not found');
            return null;
        }
        // Create Redis Client instance with you configuration settings
        $this->_redis = new YcfRedis(YcfCore::$_settings['Redis']);
        return $this->_redis;
    }
}
