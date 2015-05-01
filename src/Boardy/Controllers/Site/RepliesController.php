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
use Symfony\Component\Validator\Constraints as Assert;

class RepliesController {

	public function view(Request $request, Application $app, $board_slug, $post_id, $reply_id) {
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

		$post_values = array(
			'id' => $post_id,
			'board' => $board->id
		);

		if (!$post = $app['posts']->fetch($post_values)) {
			return new Response($app['twig']->render('@theme/errors/404.html', array(
				'page_title' => 'Page not found'
			)), 404);
		}

		$reply_values = array(
			'id' => $reply_id,
			'post' => $post_id
		);

		if (!$reply = $app['replies']->fetch($reply_values)) {
			return new Response($app['twig']->render('@theme/errors/404.html', array(
				'page_title' => 'Page not found'
			)), 404);
		}

		$board = $board->getAttributes();

		$vars['page_title'] = $post['name'];
		$vars['board'] = $board;
		$vars['post'] = $post;
		$vars['reply'] = $reply;

		return $app['twig']->render('@theme/reply/view.html', $vars);
	}

	public function edit(Request $request, Application $app, $board_slug, $post_id, $reply_id) {
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

		$post_values = array(
			'id' => $post_id,
			'board' => $board->id
		);

		if (!$post = $app['posts']->fetch($post_values)) {
			return new Response($app['twig']->render('@theme/errors/404.html', array(
				'page_title' => 'Page not found'
			)), 404);
		}

		$reply_values = array(
			'id' => $reply_id,
			'post' => $post_id
		);

		if (!$reply = $app['replies']->fetch($reply_values)) {
			return new Response($app['twig']->render('@theme/errors/404.html', array(
				'page_title' => 'Page not found'
			)), 404);
		}

		$form = $app['form.factory']->createNamedBuilder(null, 'form')
			->add('content', 'textarea', array(
				'constraints' => array(
					new Assert\NotBlank(),
					new Assert\Length(array('min' => 5))
				),
				'label' => false,
				'data' => $reply['content']
			))
			->getForm();

		$form->handleRequest($request);

		if ($form->isValid()) {
			$data = $form->getData();

			$data['id'] = $reply_id;
			$data['post'] = $post['id'];

			$reply = $app['replies']->edit($data);

			return $app->redirect($app['url_generator']->generate('post.view', array(
				'board_slug' => $board['slug'],
				'post_id' => $post['id'],
				'post_slug' => $post['slug']
			)) . '#reply-' . $reply->id);
		}

		$board = $board->getAttributes();

		$vars['page_title'] = $post['name'];
		$vars['board'] = $board;
		$vars['post'] = $post;
		$vars['reply'] = $reply;
		$vars['reply_form'] = $form->createView();

		return $app['twig']->render('@theme/reply/edit.html', $vars);
	}

	public function delete(Request $request, Application $app, $board_slug, $post_id, $reply_id) {
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

		$post_values = array(
			'id' => $post_id,
			'board' => $board->id
		);

		if (!$post = $app['posts']->fetch($post_values)) {
			return new Response($app['twig']->render('@theme/errors/404.html', array(
				'page_title' => 'Page not found'
			)), 404);
		}

		$reply_values = array(
			'id' => $reply_id,
			'post' => $post_id
		);

		if (!$reply = $app['replies']->fetch($reply_values)) {
			return new Response($app['twig']->render('@theme/errors/404.html', array(
				'page_title' => 'Page not found'
			)), 404);
		}

		$form = $app['form.factory']->createNamedBuilder(null, 'form')
			->add('delete', 'hidden')
			->getForm();

		$form->handleRequest($request);

		if ($form->isValid()) {
			$app['replies']->delete($reply['id']);
			$app['flashbag']->add('message', 'Reply deleted!');

			return $app->redirect($app['url_generator']->generate('post.view.id', array(
				'board_slug' => $board['slug'],
				'post_id' => $post['id']
			)));
		}

		$board = $board->getAttributes();

		$vars['page_title'] = $post['name'];
		$vars['board'] = $board;
		$vars['post'] = $post;
		$vars['reply'] = $reply;
		$vars['reply_form'] = $form->createView();

		return $app['twig']->render('@theme/reply/delete.html', $vars);
	}

}