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
use Boardy\Models\Posts;

class BoardsProvider implements ServiceProviderInterface {

	protected $app;

	private $allowed_permissions = array('r', 'w', 'a');

	public function register(Application $app) {
		$this->app = $app;
		$app['boards'] = $this;
	}

	public function boot(Application $app) {

	}

	public function create($data) {
		foreach ($data['permissions'] as $i => $permission) {
			if (!in_array($permission, $this->allowed_permissions)) {
				unset($data['permissions'][$i]);
			}
		}

		$board = new Boards();

		$board->name = $data['name'];
		$board->description = preg_replace('/\s+/', ' ', $data['description']);
		$board->slug = $data['slug'];
		$board->category = $data['category'];
		$board->permissions = implode('/', $data['permissions']);
		$board->save();

		return $board;
	}

	public function edit($data) {
		if (!$board = Boards::find($data['id'])) {
			return false;
		}

		foreach ($data['permissions'] as $i => $permission) {
			if (!in_array($permission, $this->allowed_permissions)) {
				unset($data['permissions'][$i]);
			}
		}

		$board->name = $data['name'];
		$board->description = preg_replace('/\s+/', ' ', $data['description']);
		$board->slug = $data['slug'];
		$board->category = $data['category'];
		$board->permissions = implode('/', $data['permissions']);
		$board->save();

		return $board;
	}

	public function delete($data) {
		if ($board = Boards::find($data['id'])) {
			$posts = Posts::where('board', '=', $data['id']);

			if ($data['action'] != '0') {
				$posts->update(array(
					'board' => $data['action']
				));
			}
			
			$posts->delete();
			return $board->delete();
		}

		return false;
	}

	public function all() {
		$all = array();

		foreach (Boards::all() as $board) {
			$all[] = $board->getAttributes();
		}

		return $all;
	}

	public function byId($id) {
		if (!$board = Boards::where('id', '=', $id)->get()->first()) {
			return false;
		}

		return $board->getAttributes();
	}

	public function bySlug($slug) {
		return Boards::where('slug', '=', $slug)->get()->first();
	}

	public function byCategory($id) {
		$data = Boards::where('category', '=', $id)->get();
		$boards = array();

		foreach ($data as $i => $board) {
			if (!$this->app['user']->hasPermission('read', $board->permissions)) {
				continue;
			}

			$board = $board->getAttributes();

			$board['posts_count'] = $this->app['posts']->byBoard($board['id'])->count();
			$boards[] = $board;
		}

		return $boards;
	}

}