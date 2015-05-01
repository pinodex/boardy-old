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

define('BOARDY', dirname(dirname(__DIR__)) . '/src');
define('ROOT', dirname(__FILE__) . '/src');

require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';

$app = require ROOT . '/installer.php';
$app->run();