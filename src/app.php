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

use Silex\Application;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Silex\Provider\SwiftmailerServiceProvider;
use Silex\Provider\HttpFragmentServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\WebProfilerServiceProvider;

use Symfony\Component\Debug\ErrorHandler;
use Symfony\Component\Debug\ExceptionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;

use BitolaCo\Silex\CapsuleServiceProvider;
use Whoops\Provider\Silex\WhoopsServiceProvider;
use EWZ\Bundle\RecaptchaBundle\Bridge\RecaptchaServiceProvider;
use Aptoma\Twig\Extension\MarkdownExtension;
use Aptoma\Twig\Extension\MarkdownEngine;
use Cocur\Slugify\Bridge\Silex\SlugifyServiceProvider;

use Boardy\Providers\ConfigurationsProvider;
use Boardy\Providers\AppVersionProvider;
use Boardy\Providers\UsersProvider;
use Boardy\Providers\UserAuthProvider;
use Boardy\Providers\BoardCategoriesProvider;
use Boardy\Providers\BoardsProvider;
use Boardy\Providers\PostsProvider;
use Boardy\Providers\RepliesProvider;
use Boardy\Providers\ThemesProvider;

$app = new Application();

if (!file_exists(APP . 'config.php')) {
	$app->get('/', function() use ($app) {
		// Simulating error 500.
	});

	return $app;
}

$app->register(new TwigServiceProvider());
$app->register(new FormServiceProvider());
$app->register(new UrlGeneratorServiceProvider());
$app->register(new SessionServiceProvider());

require APP . 'config.php';

date_default_timezone_set($app['timezone']);

if ($app['debug']) {
	$app->register(new WhoopsServiceProvider());
} else {
	error_reporting(0);

	ErrorHandler::register(0);
	ExceptionHandler::register(0);
}

if ($app['enable_profiler']) {
	$app->register(new HttpFragmentServiceProvider());
	$app->register(new ServiceControllerServiceProvider());
	$app->register(new WebProfilerServiceProvider());
}

$app->register(new CapsuleServiceProvider(), array(
	'capsule.connection' => $app['db.config']
));

$app['capsule'];

$app->register(new ConfigurationsProvider());
$app->register(new TranslationServiceProvider(), array(
	'translator.messages' => array()
));
$app->register(new ValidatorServiceProvider());
$app->register(new RecaptchaServiceProvider(), array(
	'ewz_recaptcha.public_key' => $app['configurations']->get('recaptcha_public'),
	'ewz_recaptcha.private_key' => $app['configurations']->get('recaptcha_private')
));
$app->register(new SwiftmailerServiceProvider());
$app->register(new SlugifyServiceProvider());

$app['markdown_engine'] = new MarkdownEngine\PHPLeagueCommonMarkEngine();
$app['session.storage.handler'] = $app->share(function () use ($app) {
	return new PdoSessionHandler(
		$app['capsule']->connection()->getPdo(),
		$app['session.db_options'],
		$app['session.storage.options']
	);
});
$app['flashbag'] = $app->share(function () use ($app) {
	return $app['session']->getFlashBag();
});

$app->register(new AppVersionProvider());
$app->register(new UsersProvider());
$app->register(new UserAuthProvider());
$app->register(new BoardCategoriesProvider());
$app->register(new BoardsProvider());
$app->register(new PostsProvider());
$app->register(new RepliesProvider());
$app->register(new ThemesProvider());

$app['twig'] = $app->extend('twig', function ($twig, $app) {
	
	$twig->addFunction(new \Twig_SimpleFunction('asset', function ($file) use ($app) {
		if ($app['assets.base']) {
			return $app['assets.base'] . $app['assets.path'] . '/' . ltrim($file, '/');
		}

		return $app['request_stack']->getMasterRequest()->getBasepath() . $app['assets.path'] . '/' . ltrim($file, '/');
	}));

	$twig->addFunction(new \Twig_SimpleFunction('theme_asset', function ($file) use ($app) {
		if ($app['assets.base']) {
			return $app['assets.base'] . $app['assets.path'] . '/theme/' . $app['theme_name'] . '/' . ltrim($file, '/');
		}

		return $app['request_stack']->getMasterRequest()->getBasepath() . $app['assets.path'] . '/theme/' . 
		$app['configurations']->get('theme') . '/' . ltrim($file, '/');
	}));

	$twig->addFunction(new \Twig_SimpleFunction('flashbag', function ($name) use ($app) {
		return $app['flashbag']->get($name);
	}));

	$twig->addFilter(new \Twig_SimpleFilter('md5', function ($string) {
		return hash('md5', $string);
	}));

	$twig->addExtension(new MarkdownExtension($app['markdown_engine']));

	return $twig;
});

$app['twig.loader.filesystem']->addPath(APP . 'views/', 'base');
$app['twig.loader.filesystem']->addPath(APP . 'views/admin/', 'admin');
$app['twig.loader.filesystem']->addPath(APP . 'themes/' . $app['configurations']->get('theme') . '/', 'theme');

$app['session']->start();

require APP . 'routes.php';
return $app;