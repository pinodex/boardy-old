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

namespace Boardy\Controllers\Admin;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class MainController {

	public function index(Request $request, Application $app) {
		$vars['page_title'] = 'Dashboard';

		return $app['twig']->render('@admin/index.html', $vars);
	}

	public function fourohthree(Request $request, Application $app) {
		$vars['page_title'] = 'Uh, oh!';

		return $app['twig']->render('@admin/auth/not_admin.html', $vars);
	}

}