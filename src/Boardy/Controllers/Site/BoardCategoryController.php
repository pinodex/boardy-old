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
use Boardy\Models\BoardCategories;

class BoardCategoryController {

	public function category(Request $request, Application $app, $slug) {
		if (!$category = BoardCategories::where('slug', '=', $slug)->get()->first()) {
			return new Response($app['twig']->render('@theme/errors/404.html', array(
				'page_title' => 'Page not found'
			)), 404);
		}

		$category = $category->getAttributes();

		$vars['page_title'] = $category['name'];
		$vars['category'] = $category;
	
		return $app['twig']->render('@theme/category.html', $vars);
	}

}