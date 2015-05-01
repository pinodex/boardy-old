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

class BoardCategoriesControllerProvider implements ControllerProviderInterface {

	public function connect(Application $app) {
		$controller = $app['controllers_factory'];

		$controller->get('/{slug}', 'Boardy\Controllers\Site\BoardCategoryController::category')
			->bind('category');

		return $controller;
	}

}