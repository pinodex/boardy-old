<?php

/**
 * phpSysMon
 *
 * PHP system monitoring script for Linux, also on Windows (sort-of).
 *
 * @package  phpsysmon
 * @author   Raphael Marco <pinodex@outlook.ph>
 * @link     http://pinodex.io
 */

if (php_sapi_name() === 'cli-server' && 
	is_file(__DIR__ . preg_replace('#(\?.*)$#', '', $_SERVER['REQUEST_URI']))) {
    return false;
}

define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(__DIR__) . DS);
define('APP', ROOT . 'src' . DS);
define('BOARDY_ROOT', APP . 'Boardy' . DS);

require_once ROOT . 'vendor/autoload.php';

$app = require APP . 'app.php';
$app->run();