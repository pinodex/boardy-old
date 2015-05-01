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

class BoardsControllerProvider implements ControllerProviderInterface {

	public function connect(Application $app) {
		$controller = $app['controllers_factory'];

		$controller->get('/{board_slug}', 'Boardy\Controllers\Site\BoardsController::view')
			->bind('board');

		$controller->match('/{board_slug}/create', 'Boardy\Controllers\Site\PostsController::create')
			->before(function() use ($app) {
				if (!$app['current_user']) {
					return $app->redirect($app['url_generator']->generate('site.index'));
				}
			})
			->bind('post.create');

		$controller->match('/{board_slug}/edit/{post_id}', 'Boardy\Controllers\Site\PostsController::edit')
			->before(function() use ($app) {
				if (!$app['current_user']) {
					return $app->redirect($app['url_generator']->generate('site.index'));
				}
			})
			->bind('post.edit');

		$controller->match('/{board_slug}/delete/{post_id}', 'Boardy\Controllers\Site\PostsController::delete')
			->before(function() use ($app) {
				if (!$app['current_user']) {
					return $app->redirect($app['url_generator']->generate('site.index'));
				}
			})
			->bind('post.delete');

		$controller->match('/{board_slug}/{post_id}-{post_slug}', 'Boardy\Controllers\Site\PostsController::view')
			->bind('post.view');

		$controller->get('/{board_slug}/{post_id}', 'Boardy\Controllers\Site\PostsController::view')
			->value('post_slug', null)
			->bind('post.view.id');

		$controller->get('/{board_slug}/{post_id}/replies/{reply_id}', 'Boardy\Controllers\Site\RepliesController::view')
			->bind('reply.view');

		$controller->match('/{board_slug}/{post_id}/replies/{reply_id}/edit', 'Boardy\Controllers\Site\RepliesController::edit')
			->before(function() use ($app) {
				if (!$app['current_user']) {
					return $app->redirect($app['url_generator']->generate('site.index'));
				}
			})
			->bind('reply.edit');

		$controller->match('/{board_slug}/{post_id}/replies/{reply_id}/delete', 'Boardy\Controllers\Site\RepliesController::delete')
			->before(function() use ($app) {
				if (!$app['current_user']) {
					return $app->redirect($app['url_generator']->generate('site.index'));
				}
			})
			->bind('reply.delete');

		return $controller;
	}

}