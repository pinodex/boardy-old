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
use Silex\ServiceProviderInterface;
use Boardy\Models\Boards;
use Boardy\Models\BoardCategories;

class BoardCategoriesProvider implements ServiceProviderInterface {

	protected $app;

	public function register(Application $app) {
		$this->app = $app;

		$app['board_categories'] = $this;
	}

	public function boot(Application $app) {

	}

	public function create($data) {
		$category = new BoardCategories();

		$category->name = $data['name'];
		$category->description = preg_replace('/\s+/', ' ', $data['description']);
		$category->slug = $data['slug'];
		$category->save();

		return $category;
	}

	public function edit($data) {
		if (!$category = BoardCategories::find($data['id'])) {
			return false;
		}

		$category->name = $data['name'];
		$category->description = preg_replace('/\s+/', ' ', $data['description']);
		$category->slug = $data['slug'];
		$category->save();

		return $category;
	}

	public function delete($id) {
		if ($category = BoardCategories::find($id)) {
			Boards::where('category', '=', $id)->update(array(
				'category' => '0'
			));

			return $category->delete();
		}

		return false;
	}

	public function checkSlugExistence($slug) {
		return BoardCategories::where('slug', '=', $data['slug'])->exists();
	}

	public function all() {
		$all = array();

		foreach (BoardCategories::all() as $category) {
			$all[] = $category->getAttributes();
		}

		return $all;
	}

	public function byId($id) {
		if ($category = BoardCategories::find($id)) {
			return $category->getAttributes();
		}

		return false;
	}

}