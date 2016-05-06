<?php
namespace Ycf\Model;

use Ycf\Core\YcfCore;
use Ycf\Model\ModelPdo;

class ModelTask
{
    //task 执行入口
    public static function run($serv, $task_id, $from_id, $data)
    {
        $data   = json_decode($data, true);
        $action = isset($data['action']) ? $data['action'] . 'Task' : 'notfind';
        if (method_exists('Ycf\Model\ModelTask', $action)) {
            return self::$action($serv, $task_id, $from_id, $data);
        } else {
            echo $action . ' method not find';
        }

    }

    public static function testTask($serv, $task_id, $from_id, $data)
    {

        $modelTest = new ModelPdo();
        $result    = $modelTest->testInsert();
        sleep(1);
        echo "This Task {$task_id} from Worker {$from_id}\n";
        //echo "Data: {$data}\n";
        /*for ($i = 0; $i < 10; $i++) {
        sleep(1);
        echo "Taks {$task_id} Handle {$i} times...\n";
         */
        //$fd = json_decode($data, true)['fd'];
        //$serv->send($fd, "Data in Task {$task_id}");
        echo "Task {$task_id}'s result {$result}";
    }

    public static function flushLogTask($serv, $task_id, $from_id, $data)
    {
        if (isset($data['content'])) {
            YcfCore::$_log && YcfCore::$_log->write($data['content']);
        }
        echo "Task {$task_id} have done";

    }

}
