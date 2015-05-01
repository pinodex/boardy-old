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
use Boardy\Utils\Admin;

class AdminControllerProvider implements ControllerProviderInterface {

	public function connect(Application $app) {
		require APP . 'config.admin.php';

		$controller = $app['controllers_factory'];

		$controller->get('/', 'Boardy\Controllers\Admin\MainController::index')
			->before(function() use ($app) {
				return Admin::check($app);
			})
			->bind('admin.index');

		$controller->get('/categories', 'Boardy\Controllers\Admin\BoardCategoriesController::index')
			->before(function() use ($app) {
				return Admin::check($app);
			})
			->bind('admin.categories');

		$controller->match('/categories/add', 'Boardy\Controllers\Admin\BoardCategoriesController::add')
			->before(function() use ($app) {
				return Admin::check($app);
			})
			->bind('admin.categories.add');

		$controller->match('/categories/{id}/edit', 'Boardy\Controllers\Admin\BoardCategoriesController::edit')
			->before(function() use ($app) {
				return Admin::check($app);
			})
			->bind('admin.categories.edit');

		$controller->match('/categories/{id}/delete', 'Boardy\Controllers\Admin\BoardCategoriesController::delete')
			->before(function() use ($app) {
				return Admin::check($app);
			})
			->bind('admin.categories.delete');

		$controller->get('/boards', 'Boardy\Controllers\Admin\BoardsController::index')
			->before(function() use ($app) {
				return Admin::check($app);
			})
			->bind('admin.boards');

		$controller->match('/boards/add', 'Boardy\Controllers\Admin\BoardsController::add')
			->before(function() use ($app) {
				return Admin::check($app);
			})
			->bind('admin.boards.add');

		$controller->match('/boards/{id}/edit', 'Boardy\Controllers\Admin\BoardsController::edit')
			->before(function() use ($app) {
				return Admin::check($app);
			})
			->bind('admin.boards.edit');

		$controller->match('/boards/{id}/delete', 'Boardy\Controllers\Admin\BoardsController::delete')
			->before(function() use ($app) {
				return Admin::check($app);
			})
			->bind('admin.boards.delete');

		$controller->get('/users', 'Boardy\Controllers\Admin\UsersController::index')
			->before(function() use ($app) {
				return Admin::check($app);
			})
			->bind('admin.users');

		$controller->match('/users/add', 'Boardy\Controllers\Admin\UsersController::add')
			->before(function() use ($app) {
				return Admin::check($app);
			})
			->bind('admin.users.add');

		$controller->match('/users/{id}/edit', 'Boardy\Controllers\Admin\UsersController::edit')
			->before(function() use ($app) {
				return Admin::check($app);
			})
			->bind('admin.users.edit');

		$controller->match('/users/{id}/delete', 'Boardy\Controllers\Admin\UsersController::delete')
			->before(function() use ($app) {
				return Admin::check($app);
			})
			->bind('admin.users.delete');

		$controller->match('/configurations', 'Boardy\Controllers\Admin\ConfigurationsController::index')
			->before(function() use ($app) {
				return Admin::check($app);
			})
			->bind('admin.configurations');

		$controller->get('/403', 'Boardy\Controllers\Admin\MainController::fourohthree')
			->bind('admin.not_admin');

		return $controller;
	}

}