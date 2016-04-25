<?php
namespace Ycf\Core;
use Ycf\Core\HttpServer;
use Ycf\Core\YcfCore;
use Ycf\Model\ModelTask;

class HttpServer {
	public static $instance = null;

	public $http = null;
	public static $get;
	public static $post;
	public static $header;
	public static $server;

	public $response = null;

	public function __construct() {
		$this->http = new \swoole_http_server("0.0.0.0", 9501);

		$this->http->set(
			array(
				'worker_num' => 1,
				'daemonize' => false,
				'max_request' => 1,
				'task_worker_num' => 1,
				//'dispatch_mode' => 1,
			)
		);

		$this->http->on('WorkerStart', array($this, 'onWorkerStart'));

		$this->http->on('request', function ($request, $response) {
			//捕获异常
			register_shutdown_function(array($this, 'handleFatal'));
			//请求过滤
			if ($request->server['path_info'] == '/favicon.ico' || $request->server['request_uri'] == '/favicon.ico') {
				return $response->end();
			}
			$this->response = $response;
			if (isset($request->server)) {
				self::$server = $request->server;
				foreach ($request->server as $key => $value) {
					$_SERVER[strtoupper($key)] = $value;
				}
			}
			if (isset($request->header)) {
				self::$header = $request->header;
			}
			if (isset($request->get)) {
				self::$get = $request->get;
				foreach ($request->get as $key => $value) {
					$_GET[$key] = $value;
				}
			}
			if (isset($request->post)) {
				self::$post = $request->post;
				foreach ($request->post as $key => $value) {
					$_POST[$key] = $value;
				}
			}
			if (isset($request->request_uri)) {
				$_SERVER['REQUEST_URI'] = $request->request_uri;
			}
			$GLOBALS['http_server'] = $this->http;
			ob_start();
			//实例化ycf对象
			try {
				$Ycf = new YcfCore;
				$Ycf->init();
				$Ycf->run();
			} catch (Exception $e) {
				var_dump($e);
			}
			$result = ob_get_contents();
			ob_end_clean();
			$response->end($result);
			unset($result);
		});

		$this->http->on('Task', array($this, 'onTask'));
		$this->http->on('Finish', array($this, 'onFinish'));

		$this->http->start();
	}

	public function onWorkerStart() {
		date_default_timezone_set('Asia/Shanghai');
		define('DEBUG', true);
		define('SWOOLE', true);
		define('DS', DIRECTORY_SEPARATOR);
		define('ROOT_PATH', realpath(dirname(__FILE__)) . DS . "../.." . DS);
		define('YCF_BEGIN_TIME', microtime(true));
		//echo 'worker start....';
		require 'vendor/autoload.php';

	}
	public function onTask($serv, $task_id, $from_id, $data) {
		$Ycf = new YcfCore;
		$Ycf->init();
		return ModelTask::run($serv, $task_id, $from_id, $data);
	}
	public function onFinish($serv, $task_id, $data) {
		echo "Task {$task_id} finish\n";
		echo "Result: {$data}\n";
		unset($data);
	}
	/**
	 * Fatal Error的捕获
	 *
	 */
	public function handleFatal() {
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
		YcfCore::$_log->log($log, 'fatal');
		YcfCore::$_log->sendTask();
		if ($this->response) {
			$this->response->status(500);
			$this->response->end('程序异常');
		}

		unset($this->response);
	}

	public static function getInstance() {
		if (!self::$instance) {
			self::$instance = new HttpServer();
		}
		return self::$instance;
	}
}

HttpServer::getInstance();