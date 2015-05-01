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

class SiteControllerProvider implements ControllerProviderInterface {

	public function connect(Application $app) {
		$controller = $app['controllers_factory'];

		$controller->get('/', 'Boardy\Controllers\Site\MainController::index')
			->bind('site.index');

		$controller->match('/auth/login', 'Boardy\Controllers\Site\AuthController::login')
			->before(function() use ($app) {
				if ($app['current_user']) {
					return $app->redirect($app['url_generator']->generate('site.index'));
				}
			})
			->bind('auth.login');

		$controller->match('/auth/logout', 'Boardy\Controllers\Site\AuthController::logout')
			->before(function() use ($app) {
				if (!$app['current_user']) {
					return $app->redirect($app['url_generator']->generate('site.index'));
				}
			})
			->bind('auth.logout');

		$controller->match('/auth/register', 'Boardy\Controllers\Site\AuthController::register')
			->before(function() use ($app) {
				if ($app['current_user']) {
					return $app->redirect($app['url_generator']->generate('site.index'));
				}
			})
			->bind('auth.register');

		$controller->match('/auth/verify', 'Boardy\Controllers\Site\AuthController::verify')
			->before(function() use ($app) {
				if (!$user = $app['current_user']) {
					return $app->redirect($app['url_generator']->generate('site.index'));
				}

				if ($user['acctype'] != 'UNVERIFIED') {
					return $app->redirect($app['url_generator']->generate('site.index'));
				}
			})
			->bind('auth.verify');

		$controller->match('/auth/verification/{hash}', 'Boardy\Controllers\Site\AuthController::verification')
			->before(function() use ($app) {
				if (!$user = $app['current_user']) {
					return $app->redirect($app['url_generator']->generate('site.index'));
				}

				if ($user->acctype != 'UNVERIFIED') {
					return $app->redirect($app['url_generator']->generate('site.index'));
				}
			})
			->bind('auth.verification');

		$controller->get('/auth/verified', 'Boardy\Controllers\Site\AuthController::verified')
			->bind('auth.verified');

		$controller->match('/settings/profile', 'Boardy\Controllers\Site\SettingsController::profile')
			->before(function() use ($app) {
				if (!$app['current_user']) {
					return $app->redirect($app['url_generator']->generate('site.index'));
				}
			})
			->bind('settings.profile');

		$controller->match('/settings/account', 'Boardy\Controllers\Site\SettingsController::account')
			->before(function() use ($app) {
				if (!$app['current_user']) {
					return $app->redirect($app['url_generator']->generate('site.index'));
				}
			})
			->bind('settings.account');

		return $controller;
	}

}