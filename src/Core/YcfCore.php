<?php

namespace Ycf\Core;

use Ycf\Core\YcfDB;
use Ycf\Core\YcfLog;
use Ycf\Core\YcfRedis;

class YcfCore {

	static $_settings = array();
	static $_db = null;
	static $_redis = null;
	static $_log = null;
	static $_http_server = null;

	static function init() {
		self::$_settings = parse_ini_file("settings.ini.php", true);
		self::$_log = new YcfLog();
		register_shutdown_function(array('Ycf\Core\YcfCore', 'shutdown'));

	}

	static function load($_lib) {
		switch ($_lib) {
		case '_db':
			return self::getDbInstance();
			break;
		case '_redis':
			return self::getRedisInstance();
			break;
		default:
			break;
		}
	}

	static public function getDbInstance() {
		// Create Mysql Client instance with you configuration settings
		if (self::$_db == null) {
			self::$_db = new YcfDB(self::$_settings['Mysql']);
		}
		return self::$_db;
	}
	static public function getRedisInstance() {
		if (!extension_loaded('redis')) {
			throw new \RuntimeException('php redis extension not found');
			return null;
		}
		// Create Redis Client instance with you configuration settings
		self::$_redis = new YcfRedis(self::$_settings['Redis']);
		return self::$_redis;
	}

	static public function run() {
		if (defined('SWOOLE')) {
			self::$_http_server = $GLOBALS['_http_server'];
		}
		if (php_sapi_name() == "cli" && !defined('SWOOLE')) {
			$router = self::routeCli();
		} else {
			$router = self::route();
		}
		//route to service
		$actionName = 'action' . ucfirst($router['action']);
		$ycfName = "Ycf\Service\Ycf" . ucfirst($router['service']);
		if (method_exists($ycfName, $actionName)) {
			self::init();
			$ycf = new $ycfName();
			$ycf->$actionName();
		} else {
			echo ("action not find");
		}
		self::$_log && self::$_log->flush();

	}

	static public function shutdown() {
		echo 'shutdown....';
		self::$_log->flush();
	}

	static function route() {
		$array = array('service' => 'Hello', 'action' => 'index');
		if (!empty($_GET["ycf"])) {
			$array['service'] = $_GET["ycf"];
		}
		if (!empty($_GET["act"])) {
			$array['action'] = $_GET["act"];
			return $array;
		}
		$uri = parse_url($_SERVER['REQUEST_URI']);
		if (empty($uri['path']) or $uri['path'] == '/' or $uri['path'] == '/index.php') {
			return $array;
		}
		$request = explode('/', trim($uri['path'], '/'), 3);
		if (count($request) < 2) {
			return $array;
		}
		$array['service'] = $request[0];
		$array['action'] = $request[1];

		return $array;
	}
	/**
	 *cli use this:  /opt/php7/bin/php index.php ycf=Pdo act=test
	 *
	 */
	static function routeCli() {
		$array = array('service' => 'Hello', 'action' => 'index');
		global $argv;
		foreach ($argv as $arg) {
			$e = explode("=", $arg);
			if (count($e) == 2) {
				$_GET[$e[0]] = $e[1];
			} else {
				$_GET[$e[0]] = 0;
			}
		}
		if (!empty($_GET["ycf"])) {
			$array['service'] = $_GET["ycf"];
		}
		if (!empty($_GET["act"])) {
			$array['action'] = $_GET["act"];
		}
		$_SERVER['REQUEST_URI'] = $argv[0] . "?" . $argv[1] . "&" . $argv[2];
		return $array;
	}

}
