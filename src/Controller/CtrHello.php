<?php
namespace Ycf\Controller;

use Ycf\Core\YcfCore;

class CtrHello
{

    public function actionIndex()
    {
        echo "hello ycf 456";
        //YcfCore::$_response->end("Greet, Klcoze!");

    }
    public function actionHello()
    {
        echo "hello ycf" . time();
        echo $this->getPPP();

    }

    public function actionTask()
    {
        // send a task to task worker.
        $param = array(
            'action' => 'test',
            'time'   => time(),
        );
        //var_dump(HttpServer::getInstance()->http);
        //$this->http->task(json_encode($param));
        for ($i = 0; $i < 1; $i++) {
            $taskId = YcfCore::$_http_server->task(json_encode($param));
        }
        echo $taskId . " hello ycf" . time();

    }

    public function actionLog()
    {
        //for ($i = 0; $i < 1000; $i++) {
        YcfCore::$_log->log('hello ycf' . time(), 'info');
        YcfCore::$_response->end("Greet, Klcoze!");
        //}
    }

}
