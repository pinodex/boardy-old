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

class UsersController {

	public static function view(Request $request, Application $app, $id, $username) {
		if (!$user = $app['user']->fetch('id', '=', $id, false)) {
			return new Response($app['twig']->render('@theme/errors/404.html', array(
				'page_title' => 'Page not found'
			)), 404);
		}

		if ($user['username'] != $username) {
			return $app->redirect($app['url_generator']->generate('user.view', array(
				'id' => $user['id'],
				'username' => $user['username']
			)));
		}

		$posts = $app['posts']->byUser($user['id']);
		//dd($posts);

		$vars['page_title'] = 'Profile of ' . $user['name'];
		$vars['user'] = $user;
		$vars['user']['posts'] = $posts;
		$vars['own_profile'] = false;

		if ($app['current_user'] && $app['current_user']['id'] == $user['id']) {
			$vars['own_profile'] = true;
		}

		return $app['twig']->render('@theme/user/view.html', $vars);
	}

}