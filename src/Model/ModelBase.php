<?php
use Ycf\Core\YcfDB;
use Ycf\Core\YcfRedis;

class ModelBase {
	protected $_db = null;
	protected $_redis = null;
	function __construct() {
		$this->_db = $this->load('_db');
	}

	protected function load($obj) {
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

	protected function getDbInstance() {
		// Create Mysql Client instance with you configuration settings
		if ($this->_db == null) {
			$this->_db = new YcfDB($this->_settings['Mysql']);
		}
		return $this->_db;
	}
	protected function getRedisInstance() {
		if (!extension_loaded('redis')) {
			throw new \RuntimeException('php redis extension not found');
			return null;
		}
		// Create Redis Client instance with you configuration settings
		$this->_redis = new YcfRedis($this->_settings['Redis']);
		return $this->_redis;
	}
}