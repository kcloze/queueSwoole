<?php
namespace Ycf\Controller;

use Ycf\Model\ModelRedis;

class CtrRedis
{
    public function actionTest()
    {
        $modelTest = new ModelRedis();

        $result = $modelTest->testRedis();
        var_dump($result);

    }
}
