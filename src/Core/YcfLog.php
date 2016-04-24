<?php
namespace Ycf\Core;

class YcfLog {
	const LEVEL_TRACE = 'trace';
	const LEVEL_WARNING = 'warning';
	const LEVEL_ERROR = 'error';
	const LEVEL_INFO = 'info';
	const LEVEL_PROFILE = 'profile';
	const MAX_LOGS = 10000;
	//单个类型log
	private $_logs = array();
	private $_logCount = 0;
	private $_logPath = '';

	public function formatLogMessage($message, $level, $category, $time) {
		return @date('Y/m/d H:i:s', $time) . " [$level] [$category] $message\n";
	}

	public function log($message, $level = 'info', $category = 'application', $flush = false) {
		$this->_logs[$category][] = array($message, $level, $category, microtime(true));
		$this->_logCount++;
		if ($this->_logCount >= YcfLog::MAX_LOGS || $flush == true) {
			$this->flush($category);
		}
	}

	public function processLogs() {
		$logsAll["application"] = "[" . $_SERVER['REQUEST_URI'] . "] " . "[runing time]: " . (microtime(true) - YCF_BEGIN_TIME) . "\n";
		foreach ((array) $this->_logs as $key => $logs) {
			$logsAll[$key] = '';
			foreach ((array) $logs as $log) {
				$logsAll[$key] .= $this->formatLogMessage($log[0], $log[1], $log[2], $log[3]);
			}
		}
		return $logsAll;
	}
	/**
	 *
	 * 写日志到文件
	 */
	public function flush() {

		if ($this->_logCount <= 0) {
			return false;
		}
		$logsAll = $this->processLogs();
		$this->write($logsAll);
		$this->_logs = array();
		$this->_logCount = 0;
	}
	//异步任务写日志
	public function sendTask() {
		$logsAll = $this->processLogs();
		if (empty($logsAll)) {
			return false;
		}
		$param = array(
			'action' => 'flushLog',
			'name' => '日志处理',
			'content' => $logsAll,
		);
		$taskId = YcfCore::$_http_server->task(json_encode($param));

	}
	/**
	 * [write 根据日志类型写到不同的日志文件]
	 * @return [type] [description]
	 */
	public function write($logsAll) {
		if (empty($logsAll)) {
			return;
		}

		$this->_logPath = ROOT_PATH . 'src/runtime/';

		foreach ($logsAll as $key => $value) {
			if (empty($key)) {
				continue;
			}
			$fileName = $this->_logPath . $key . '.log';
			$fp2 = fopen($fileName, "a+") or YcfUtils::exit("Log fatal Error !");
			fwrite($fp2, $value);
			fclose($fp2);
		}

	}

}