<?php
namespace Ycf\Core;
use Ycf\Core\YcfCore;

class YcfDBSwoole {
	public $config = array();
	public $result = array();

	public function __construct() {
		$this->config = YcfCore::$_settings['Mysql'];
	}
	public function Connect() {
		$db = new \mysqli;
		$db->connect($this->config['host'], $this->config['user'], $this->config['password'], $this->config['dbname'], $this->config['port'], $this->config['charset']);
		return $db;
	}

}
