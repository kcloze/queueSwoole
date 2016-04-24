<?php

namespace Ycf\Core;

use Ycf\Core\YcfLog;

class YcfCore {

	static $_settings = array();
	static $_log = null;
	static $_http_server = null;

	static function init() {
		self::$_settings = parse_ini_file("settings.ini.php", true);
		self::$_log = new YcfLog();
		var_dump("lxp: " . time());
		register_shutdown_function(array('Ycf\Core\YcfCore', 'handleFatal'));

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
		//route to controller
		$actionName = 'action' . ucfirst($router['action']);
		$ycfName = "Ycf\Controller\Ctr" . ucfirst($router['controller']);
		if (method_exists($ycfName, $actionName)) {
			self::init();
			$ycf = new $ycfName();
			$ycf->$actionName();
		} else {
			echo ("action not find");
		}
		self::$_log && self::$_log->sendTask();

	}

	/**
	 * Fatal Error的捕获
	 *
	 */
	static public function handleFatal() {
		var_dump("kcloze");
		$error = error_get_last();
		if (!isset($error['type'])) {
			return;
		}

		switch ($error['type']) {
		case E_ERROR:
		case E_PARSE:
		case E_DEPRECATED:
		case E_CORE_ERROR:
		case E_COMPILE_ERROR:
			break;
		default:
			return;
		}
		$message = $error['message'];
		$file = $error['file'];
		$line = $error['line'];
		$log = "\n异常提示：$message ($file:$line)\nStack trace:\n";
		$trace = debug_backtrace(1);

		foreach ($trace as $i => $t) {
			if (!isset($t['file'])) {
				$t['file'] = 'unknown';
			}
			if (!isset($t['line'])) {
				$t['line'] = 0;
			}
			if (!isset($t['function'])) {
				$t['function'] = 'unknown';
			}
			$log .= "#$i {$t['file']}({$t['line']}): ";
			if (isset($t['object']) && is_object($t['object'])) {
				$log .= get_class($t['object']) . '->';
			}
			$log .= "{$t['function']}()\n";
		}
		if (isset($_SERVER['REQUEST_URI'])) {
			$log .= '[QUERY] ' . $_SERVER['REQUEST_URI'];
		}
		self::$_log->log($log, 'fatal');
		if (self::$_http_server->response) {
			self::$_http_server->response->status(500);
			self::$_http_server->response->end('程序异常');
		}

		unset(self::$_http_server->response);
	}
	static public function shutdown() {
		echo 'shutdown....';
		self::$_log->sendTask();
	}

	static public function route() {
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
	static public function routeCli() {
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
