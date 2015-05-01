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

use Symfony\Component\HttpFoundation\Response;

$app->mount('/', new Boardy\Providers\SiteControllerProvider());

$app->mount('/b/', new Boardy\Providers\BoardsControllerProvider());

$app->mount('/c/', new Boardy\Providers\BoardCategoriesControllerProvider());

$app->mount('/u/', new Boardy\Providers\UsersControllerProvider());

$app->mount($app['admin.base'], new Boardy\Providers\AdminControllerProvider());

$app->error(function (\Exception $e, $code) use ($app) {
	if ($app['debug']) {
		return;
	}

	$templates = array (
		'@theme/errors/' . $code . '.html',
		'@theme/errors/' . substr($code, 0, 2) . 'x.html',
		'@theme/errors/' . substr($code, 0, 1) . 'xx.html',
		'@theme/errors/default.html',
		'@base/errors/' . $code . '.html',
		'@base/errors/' . substr($code, 0, 2) . 'x.html',
		'@base/errors/' . substr($code, 0, 1) . 'xx.html',
		'@base/errors/default.html'
	);

	$status = array (
		'400' => 'Bad Request',
		'401' => 'Unauthorized',
		'403' => 'Forbidden',
		'404' => 'Not Found',
		'500' => 'Internal Server Error'
	);

	$vars['page_title'] = @$status[$code] ? : 'Error';
	$vars['code'] = $code;

	return new Response($app['twig']->resolveTemplate($templates)->render($vars), $code);
});