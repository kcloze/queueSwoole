<?php
namespace Ycf\Service;
use Ycf\Model\AyncDb;
use Ycf\Model\ModelPdo;

class YcfPdo {

	public function actionTest() {
		$modelTest = new ModelPdo();
		$result = $modelTest->testInsert();
		var_dump($result);

		//$result = $modelTest->testQuery();
		//var_dump($result);
	}

	public function actionAync() {
		$ayncDb = new AyncDb();
		$result = $ayncDb->testInsert();
		var_dump($result);
	}

}