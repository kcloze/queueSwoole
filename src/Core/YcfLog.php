<?php
namespace Ycf\Core;

class YcfLog {
	const LEVEL_TRACE = 'trace';
	const LEVEL_WARNING = 'warning';
	const LEVEL_ERROR = 'error';
	const LEVEL_INFO = 'info';
	const LEVEL_PROFILE = 'profile';
	const MAX_LOGS = 10000;

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
		$text = array();
		foreach ((array) $this->_logs as $key => $logs) {
			$text[$key] = '';
			foreach ((array) $logs as $log) {
				$text[$key] .= $this->formatLogMessage($log[0], $log[1], $log[2], $log[3]);
			}
		}

		return $text;
	}
	/**
	 *
	 * 写日志到文件
	 */
	public function flush() {

		if ($this->_logCount <= 0) {
			return false;
		}
		$this->write();
		$this->_logs = array();
		$this->_logCount = 0;
	}
	/**
	 * [write 根据日志类型写到不同的日志文件]
	 * @return [type] [description]
	 */
	public function write() {
		$this->_logPath = ROOT_PATH . 'src/runtime/';
		$text = $this->processLogs();
		$text["application"] .= "[" . $_SERVER['REQUEST_URI'] . "] " . "[runing time]: " . (microtime(true) - YCF_BEGIN_TIME) . "\n";

		foreach ($text as $key => $value) {
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