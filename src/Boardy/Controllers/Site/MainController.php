<?php

/**
 * Boardy
 *
 * Simple PHP forum app.
 *
 * @package  boardy
 * @author   Raphael Marco <pinodex@outlook.ph>
 * @link     http://pinodex.io
 */

namespace Boardy\Controllers\Site;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class MainController {

	public function index(Request $request, Application $app) {
		$vars['page_title'] = 'Home';
	
		return $app['twig']->render('@theme/index.html', $vars);
	}

}