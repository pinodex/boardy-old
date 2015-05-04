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
use Boardy\Models\Posts;
use Boardy\Utils\Helpers;

class PostsProvider implements ServiceProviderInterface {

	protected $app;

	public function register(Application $app) {
		$this->app = $app;
		$app['posts'] = $this;
	}

	public function boot(Application $app) {
		
	}

	public function create($data) {
		$post = new Posts();
		
		$post->name = $data['title'];
		$post->slug = $this->app['slugify']->slugify(Helpers::truncateSlug($data['title']));
		$post->content = $data['content'];
		$post->tags = $data['tags'];
		$post->board = $data['board_id'];
		$post->created = date('Y-m-d H:i:s');
		$post->author = $this->app['current_user']['id'];
		$post->ip = $this->app['request_stack']->getMasterRequest()->getClientIp();
		$post->save();

		return $post;
	}

	public function edit($data) {
		if (!$post = Posts::find($data['id'])) {
			return false;
		}
		
		$post->name = $data['title'];
		$post->slug = $this->app['slugify']->slugify(Helpers::truncateSlug($data['title']));
		$post->content = $data['content'];
		$post->tags = $data['tags'];
		$post->last_edited = date('Y-m-d H:i:s');
		$post->save();

		return $post;
	}

	public function delete($id) {
		if ($post = Posts::find($id)) {
			return $post->delete();
		}

		return false;
	}

	public function bySlug($slug) {
		return Posts::where('slug', '=', $slug)->get()->first()->getAttributes();
	}

	public function byBoard($board) {
		return Posts::where('board', '=', $board);
	}

	public function byUser($id) {
		$raw_posts = $this->get('author', '=', $id)['posts'];
		$posts = array();

		foreach ($raw_posts as $i => $post) {
			$posts[$i] = $post;
			$posts[$i]['board'] = $this->app['boards']->byId($post['board']);
		}

		return $posts;
	}

	public function get($column, $comparator, $value) {
		$posts = Posts::where($column, $comparator, $value)->orderBy('id', 'DESC');
		$output = array(
			'posts' => array()
		);

		foreach ($posts->get() as $i => $post) {
			$output['posts'][$i] = $post->getAttributes();
			$output['posts'][$i]['author'] = $this->app['user']->fetch('id', '=', $post['author']);
		}

		return $output;
	}

	public function fetch($values, $limit = null) {
		$post = Posts::where($values);

		if (!$post = $post->get()->first()) {
			return false;
		}

		$post = $post->getAttributes();
		$post['author'] = $this->app['user']->fetch('id', '=', $post['author']);

		return $post;
	}

	public function canEdit($post) {
		if (!$user = $this->app['current_user']) {
			return false;
		}

		if ($user['acctype'] == 'ADMIN' || $user['acctype'] == 'MODERATOR') {
			return true;
		}

		if ($user['id'] == $post) {
			return true;
		}

		return false;
	}

}