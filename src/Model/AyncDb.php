<?php
/*
 *异步mysql模型类-示例
 */
namespace Ycf\Model;
use Ycf\Core\YcfDBSwoole;

class AyncDb extends YcfDBSwoole {

	public function __construct() {
		parent::__construct();
		$this->startTime = microtime(true);

	}
	public function testInsert() {

		//$this->Query("INSERT INTO pdo_test( pName,pValue) VALUES ( 'fww2','总过万佛无法')", 'callback');
		$this->Query("select  *  from pdo_test limit 3", 'callback');

	}
	public function Query($sql, $callback) {
		var_dump($sql);
		$db = $this->Connect();
		\swoole_mysql_query($db, $sql, array($this, $callback));
	}

	public function callback($db, $r) {
		//SQL执行失败了
		if ($r == false) {
			$result['_error'] = $db->_error;
			$result['_errno'] = $db->_errno;
		}
		//执行成功，update/delete/insert语句，没有结果集
		elseif ($r === true) {
			//echo "count=" . count($r) . ", time=" . (microtime(true) - $this->startTime), "\n";
			$result['_affected_rows'] = $db->_affected_rows;
			$result['_insert_id'] = $db->_insert_id;
		}
		//执行成功，$r是结果集数组
		else {
			//echo "count=" . count($r) . ", time=" . (microtime(true) - $this->startTime), "\n";
			$result['_result'] = $r;
		}
		var_dump($db, $r);
		//$db->close();
	}

	//从mysql连接池中拿数据
	public function getMysqlPools($sql) {
		$client = new swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_SYNC); //同步阻塞
		$client->connect('127.0.0.1', 9509, 0.5, 0);
		$client->send($sql);
		return $client->recv();
	}

}