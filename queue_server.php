<?php
//namespace Ycf\Swoole;
//use Ycf\Core\YcfCore;

class HttpServer {
	public static $instance;

	public $http;
	public static $get;
	public static $post;
	public static $header;
	public static $server;

	public function __construct() {
		$this->http = new swoole_http_server("0.0.0.0", 9501);

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

			if (isset($request->server)) {
				HttpServer::$server = $request->server;
				foreach ($request->server as $key => $value) {
					$_SERVER[strtoupper($key)] = $value;
				}
			}
			if (isset($request->header)) {
				HttpServer::$header = $request->header;
			}
			if (isset($request->get)) {
				HttpServer::$get = $request->get;
				foreach ($request->get as $key => $value) {
					$_GET[$key] = $value;
				}
			}
			if (isset($request->post)) {
				HttpServer::$post = $request->post;
				foreach ($request->post as $key => $value) {
					$_POST[$key] = $value;
				}
			}
			if (isset($request->request_uri)) {
				$_SERVER['REQUEST_URI'] = $request->request_uri;
			}

			$GLOBALS['_http_server'] = $this->http;

			ob_start();
			//实例化ycf对象
			try {
				Ycf\Core\YcfCore::run();
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
		define('ROOT_PATH', realpath(dirname(__FILE__)) . DS);
		define('YCF_BEGIN_TIME', microtime(true));
		//echo 'worker start....';
		require 'vendor/autoload.php';

	}
	public function onTask($serv, $task_id, $from_id, $data) {
		Ycf\Core\YcfCore::init();
		return Ycf\Model\ModelTask::run($serv, $task_id, $from_id, $data);
	}
	public function onFinish($serv, $task_id, $data) {
		echo "Task {$task_id} finish\n";
		echo "Result: {$data}\n";
		unset($data);
	}

	public static function getInstance() {
		if (!self::$instance) {
			self::$instance = new HttpServer();
		}
		return self::$instance;
	}
}

HttpServer::getInstance();