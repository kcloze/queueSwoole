<?php
namespace Ycf\Service;
use Ycf\Model\ModelRedis;

class YcfRedis {
	public function actionTest() {
		$modelTest = new ModelRedis();

		$result = $modelTest->testRedis();
		var_dump($result);

	}
}