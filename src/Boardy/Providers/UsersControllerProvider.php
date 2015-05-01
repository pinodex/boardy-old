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

class UsersControllerProvider implements ControllerProviderInterface {

	public function connect(Application $app) {
		$controller = $app['controllers_factory'];

		$controller->get('/', 'Boardy\Controllers\Site\UsersController::index')
			->bind('user.index');

		$controller->get('/{id}-{username}', 'Boardy\Controllers\Site\UsersController::view')
			->bind('user.view');

		$controller->get('/{id}', 'Boardy\Controllers\Site\UsersController::view')
			->value('username', null)
			->bind('user.view.id');

		return $controller;
	}

}