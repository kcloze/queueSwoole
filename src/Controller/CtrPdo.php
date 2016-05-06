<?php
namespace Ycf\Controller;

use Ycf\Model\ModelPdo;
use Ycf\Model\ModelProxyDb;

class CtrPdo
{

    public function actionTest()
    {
        $modelTest = new ModelPdo();
        $result    = $modelTest->testInsert();
        var_dump($result);

        //$result = $modelTest->testQuery();
        //var_dump($result);
    }

    public function actionAync()
    {
        $result = ModelProxyDb::query('select  *  from pdo_test limit 1');
        var_dump($result);
    }

}
