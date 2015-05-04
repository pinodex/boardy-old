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
use Boardy\Models\Replies;

class RepliesProvider implements ServiceProviderInterface {

	protected $app;

	public $all = array();

	public function register(Application $app) {
		$this->app = $app;
		$app['replies'] = $this;
	}

	public function boot(Application $app) {
		
	}

	public function create($data) {
		$reply = new Replies();

		$reply->post = $data['post'];
		$reply->content = $data['content'];
		$reply->created = date('Y-m-d H:i:s');
		$reply->author = $this->app['current_user']['id'];
		$reply->ip = $this->app['request_stack']->getMasterRequest()->getClientIp();
		$reply->save();

		return $reply;
	}

	public function edit($data) {
		if (!$reply = Replies::find($data['id'])) {
			return false;
		}

		$reply->content = $data['content'];
		$reply->last_edited = date('Y-m-d H:i:s');
		$reply->ip = $this->app['request_stack']->getMasterRequest()->getClientIp();
		$reply->save();

		return $reply;
	}

	public function delete($id) {
		if ($reply = Replies::find($id)) {
			return $reply->delete();
		}

		return false;
	}

	public function byUser($id) {
		$raw_replies = $this->get('author', '=', $id)['replies'];
		$replies = array();

		foreach ($raw_replies as $i => $reply) {
			$replies[$i] = $reply;
		}

		return $replies;
	}

	public function get($column, $comparator, $value) {
		$replies = Replies::where($column, $comparator, $value)->orderBy('id', 'ASC');
		$output = array(
			'replies' => array()
		);

		foreach ($replies->get() as $i => $post) {
			$output['replies'][$i] = $post->getAttributes();
			$output['replies'][$i]['author'] = $this->app['user']->fetch('id', '=', $post['author']);
		}
		
		return $output;
	}

	public function fetch($values, $limit = null) {
		$reply = Replies::where($values);

		if (!$reply = $reply->get()->first()) {
			return false;
		}

		$reply = $reply->getAttributes();
		$reply['author'] = $this->app['user']->fetch('id', '=', $reply['author']);

		return $reply;
	}

}