<?php
date_default_timezone_set('Asia/Shanghai');
define('DEBUG', true);
define('DS', DIRECTORY_SEPARATOR);
define('ROOT_PATH', realpath(dirname(__FILE__)) . DS);
define('YCF_BEGIN_TIME', microtime(true));
//echo 'worker start....';
require 'vendor/autoload.php';

use Ycf\Core\YcfCore;
$ycf = new YcfCore;
$ycf->init();
$ycf->run();
