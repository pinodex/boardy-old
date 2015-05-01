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
use Symfony\Component\HttpFoundation\Response;
use Boardy\Models\Configurations;

class BoardsController {

	public function view(Request $request, Application $app, $board_slug) {
		if (!$board = $app['boards']->bySlug($board_slug)) {
			return new Response($app['twig']->render('@theme/errors/404.html', array(
				'page_title' => 'Page not found'
			)), 404);
		}

		if (!$app['user']->hasPermission('read', $board->permissions)) {
			return new Response($app['twig']->render('@theme/errors/403.html', array(
				'page_title' => 'Access denied'
			)), 403);
		}

		$limit = $app['configurations']->get('posts_per_page', 20);
		$page = (int) $request->query->get('page', '1');
		$board = $board->getAttributes();

		$vars['page_title'] = $board['name'];
		$vars['board'] = $app['posts']->get('board' , '=', $board['id'], $page, $limit);
		$vars['board']['meta'] = $board;

		return $app['twig']->render('@theme/board.html', $vars);
	}

}