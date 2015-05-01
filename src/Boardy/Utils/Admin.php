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


namespace Boardy\Utils;

use Silex\Application;
use Symfony\Component\HttpFoundation\Response;

class Admin {

	public static function check(Application $app) {
		if (!$app['current_user']) {
			return $app->redirect($app['url_generator']->generate('auth.login', array(
				'to' => $app['request_stack']->getMasterRequest()->getRequestUri()
			)));
		}

		if ($app['current_user']['acctype'] != 'ADMIN') {
			return new Response($app['twig']->render('@admin/auth/not_admin.html', array(
				'page_title' => 'Page not found'
			)), 404);
		}
	}
	
}