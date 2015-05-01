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

namespace Boardy\Providers;

use Silex\Application;
use Silex\ControllerProviderInterface;

class InstallControllerProvider implements ControllerProviderInterface {

	public function connect(Application $app) {
		$controller = $app['controllers_factory'];
		
		$controller->get('/', 'Boardy\Controllers\Install\MainController::index');

		return $controller;
	}

}