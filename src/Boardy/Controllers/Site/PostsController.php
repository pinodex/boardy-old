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
use Boardy\Models\Configurations;

class PostsController {

	public function create(Request $request, Application $app, $board_slug) {
		if (!$board = $app['boards']->bySlug($board_slug)) {
			return new Response($app['twig']->render('@theme/errors/404.html', array(
				'page_title' => 'Page not found'
			)), 404);
		}

		if (!$app['user']->hasPermission('write', $board->permissions)) {
			return new Response($app['twig']->render('@theme/errors/403.html', array(
				'page_title' => 'Access denied'
			)), 403);
		}

		$board = $board->getAttributes();

		$form = $app['form.factory']->createNamedBuilder(null, 'form')
			->add('title', 'text', array(
				'constraints' => array(
					new Assert\NotBlank(),
					new Assert\Length(array('min' => 5))
				)
			))
			->add('content', 'textarea', array(
				'constraints' => array(
					new Assert\NotBlank(),
					new Assert\Length(array('min' => 16))
				)
			))
			->add('tags', 'text', array(
				'attr' => array(
					'placeholder' => 'Optional. Separate by comma.'
				),
				'required' => false
			))
			->getForm();

		$form->handleRequest($request);

		if ($form->isValid()) {
			$data = $form->getData();

			$data['board_id'] = $board['id'];

			$post = $app['posts']->create($data);

			return $app->redirect($app['url_generator']->generate('post.view', array(
				'board_slug' => $board['slug'],
				'post_id' => $post->id,
				'post_slug' => $post->slug
			)));
		}

		$vars['page_title'] = 'Create new post';
		$vars['board'] = $board;
		$vars['post_form'] = $form->createView();

		return $app['twig']->render('@theme/post/create.html', $vars);
	}

	public function view(Request $request, Application $app, $board_slug, $post_id, $post_slug) {
		if ($request->getMethod() == 'POST' && !$app['current_user']) {
			return $app->redirect($app['url_generator']->generate('site.index'));
		}

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

		$values = array(
			'id' => $post_id,
			'board' => $board->id
		);

		if (!$post = $app['posts']->fetch($values)) {
			return new Response($app['twig']->render('@theme/errors/404.html', array(
				'page_title' => 'Page not found'
			)), 404);
		}

		if ($post['slug'] != $post_slug) {
			return $app->redirect($app['url_generator']->generate('post.view', array(
				'board_slug' => $board['slug'],
				'post_id' => $post_id,
				'post_slug' => $post['slug']
			)));
		}

		$form = $app['form.factory']->createNamedBuilder(null, 'form')
			->add('content', 'textarea', array(
				'constraints' => array(
					new Assert\NotBlank(),
					new Assert\Length(array('min' => 5))
				),
				'label' => false
			))
			->getForm();

		$form->handleRequest($request);

		if ($form->isValid()) {
			$data = $form->getData();

			$data['post'] = $post['id'];
			$data['author'] = $app['current_user']['id'];

			$reply = $app['replies']->create($data);

			return $app->redirect($app['url_generator']->generate('post.view', array(
				'board_slug' => $board['slug'],
				'post_id' => $post['id'],
				'post_slug' => $post['slug']
			)) . '#reply-' . $reply->id);
		}

		$board = $board->getAttributes();
		//$limit = $app['configurations']->app('replies_per_page', 20);
		$limit = null;
		$page = (int) $request->query->get('page', '1');
		$replies = $app['replies']->get('post', '=', $post['id']);

		$vars['page_title'] = $post['name'];
		$vars['board'] = $board;
		$vars['post'] = $post;
		$vars['replies'] = $replies['replies'];
		$vars['pages'] = $replies['pages'];
		$vars['reply_form'] = $form->createView();

		return $app['twig']->render('@theme/post/view.html', $vars);
	}

	public function edit(Request $request, Application $app, $board_slug, $post_id) {
		if (!$board = $app['boards']->bySlug($board_slug)) {
			return new Response($app['twig']->render('@theme/errors/404.html', array(
				'page_title' => 'Page not found'
			)), 404);
		}

		if (!$app['user']->hasPermission('write', $board->permissions)) {
			return new Response($app['twig']->render('@theme/errors/403.html', array(
				'page_title' => 'Access denied'
			)), 403);
		}

		$values = array(
			'id' => $post_id,
			'board' => $board->id
		);

		if (!$post = $app['posts']->fetch($values)) {
			return new Response($app['twig']->render('@theme/errors/404.html', array(
				'page_title' => 'Page not found'
			)), 404);
		}

		if ($app['current_user']['id'] != $post['author']['id']) {
			return new Response($app['twig']->render('@theme/errors/403.html', array(
				'page_title' => 'Access denied'
			)), 403);
		}

		$form = $app['form.factory']->createNamedBuilder(null, 'form')
			->add('title', 'text', array(
				'constraints' => array(
					new Assert\NotBlank(),
					new Assert\Length(array('min' => 5))
				),
				'data' => $post['name']
			))
			->add('content', 'textarea', array(
				'constraints' => array(
					new Assert\NotBlank(),
					new Assert\Length(array('min' => 16))
				),
				'data' => $post['content']
			))
			->add('tags', 'text', array(
				'attr' => array(
					'placeholder' => 'Optional. Separate by comma.'
				),
				'data' => $post['tags'],
				'required' => false
			))
			->getForm();

		$form->handleRequest($request);

		if ($form->isValid()) {
			$data = $form->getData();
			$data['id'] = $post['id'];

			$post = $app['posts']->edit($data);
			$app['flashbag']->add('message', 'Post updated!');

			return $app->redirect($app['url_generator']->generate('post.view', array(
				'board_slug' => $board['slug'],
				'post_id' => $post->id,
				'post_slug' => $post->slug
			)));
		}

		$vars['page_title'] = 'Editing ' . $post['name'];
		$vars['board'] = $board;
		$vars['post'] = $post;
		$vars['post_form'] = $form->createView();

		return $app['twig']->render('@theme/post/edit.html', $vars);
	}

	public function delete(Request $request, Application $app, $board_slug, $post_id) {
		if (!$board = $app['boards']->bySlug($board_slug)) {
			return new Response($app['twig']->render('@theme/errors/404.html', array(
				'page_title' => 'Page not found'
			)), 404);
		}

		if (!$app['user']->hasPermission('write', $board->permissions)) {
			return new Response($app['twig']->render('@theme/errors/403.html', array(
				'page_title' => 'Access denied'
			)), 403);
		}

		$values = array(
			'id' => $post_id,
			'board' => $board->id
		);

		if (!$post = $app['posts']->fetch($values)) {
			return new Response($app['twig']->render('@theme/errors/404.html', array(
				'page_title' => 'Page not found'
			)), 404);
		}

		if ($app['current_user']['id'] != $post['author']['id']) {
			return new Response($app['twig']->render('@theme/errors/403.html', array(
				'page_title' => 'Access denied'
			)), 403);
		}

		$form = $app['form.factory']->createNamedBuilder(null, 'form')
			->add('delete', 'hidden')
			->getForm();

		$form->handleRequest($request);

		if ($form->isValid()) {
			$app['posts']->delete($post['id']);
			$app['flashbag']->add('message', 'Post deleted!');

			return $app->redirect($app['url_generator']->generate('board', array(
				'board_slug' => $board['slug']
			)));
		}

		$vars['page_title'] = 'Delete ' . $post['name'];
		$vars['board'] = $board;
		$vars['post'] = $post;
		$vars['post_form'] = $form->createView();

		return $app['twig']->render('@theme/post/delete.html', $vars);
	}

}