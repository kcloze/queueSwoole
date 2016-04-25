<?php

namespace Ycf\Core;

use Ycf\Core\YcfLog;

//use Ycf\Core\YcfCore;

class YcfCore {

	static $_settings = array();
	static $_log = null;
	static $_http_server = null;
	public $_response = null;

	public function init() {
		self::$_settings = parse_ini_file("settings.ini.php", true);
		self::$_log = new YcfLog();
		self::$_http_server = $GLOBALS['http_server'];
		var_dump("lxp: " . time());

		//register_shutdown_function(array('Ycf\Core\YcfCore', 'shutdown'));

	}

	public function run() {

		if (php_sapi_name() == "cli" && !defined('SWOOLE')) {
			$router = $this->routeCli();
		} else {
			$router = $this->route();
		}
		//route to controller
		$actionName = 'action' . ucfirst($router['action']);
		$ycfName = "Ycf\Controller\Ctr" . ucfirst($router['controller']);
		if (method_exists($ycfName, $actionName)) {
			$ycf = new $ycfName();
			$ycf->$actionName();
		} else {
			echo ("action not find");
		}
		$this->shutdown();

	}

	public function shutdown() {
		echo 'shutdown....';
		self::$_log->sendTask();
	}

	public function route() {
		$array = array('controller' => 'Hello', 'action' => 'index');
		if (!empty($_GET["ycf"])) {
			$array['controller'] = $_GET["ycf"];
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
		$array['controller'] = $request[0];
		$array['action'] = $request[1];

		return $array;
	}
	/**
	 *cli use this:  /opt/php7/bin/php index.php ycf=Pdo act=test
	 *
	 */
	public function routeCli() {
		$array = array('controller' => 'Hello', 'action' => 'index');
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
			$array['controller'] = $_GET["ycf"];
		}
		if (!empty($_GET["act"])) {
			$array['action'] = $_GET["act"];
		}
		$_SERVER['REQUEST_URI'] = $argv[0] . "?" . $argv[1] . "&" . $argv[2];
		return $array;
	}

}
