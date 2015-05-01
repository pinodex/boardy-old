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

namespace Boardy\Controllers\Admin;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Boardy\Constraints as CustomAssert;
use Boardy\Models\Boards;

class BoardsController {

	public function index(Request $request, Application $app) {
		$vars['page_title'] = 'Categories';
		$vars['categories'] = $app['board_categories']->all();

		return $app['twig']->render('@admin/boards/index.html', $vars);
	}

	public function add(Request $request, Application $app) {
		$vars['page_title'] = 'Add Board';

		$categories = array();

		foreach ($app['board_categories']->all() as $category) {
			$categories[$category['id']] = $category['name'];
		}

		$categories['0'] = 'Unassigned';

		foreach ($app['boards']->all() as $boards) {
			$vars['extra_data']['existing_slugs'][] = $boards['slug'];
		}

		$form = $app['form.factory']->createNamedBuilder(null, 'form')
			->add('name', 'text', array(
				'constraints' => array(
					new Assert\NotBlank(),
					new Assert\Length(array('min' => 5))
				)
			))
			->add('slug', 'text', array(
				'constraints' => array(
					new Assert\NotBlank(),
					new CustomAssert\RecordExistence(array(
						'validate' => 'exists',
						'model' => new Boards(),
						'row' => 'slug',
						'message' => 'Slug already exists'
					))
				)
			))
			->add('description', 'textarea', array(
				'constraints' => array(
					new Assert\NotBlank(),
					new Assert\Length(array('min' => 5))
				)
			))
			->add('category', 'choice', array(
				'choices' => $categories
			))
			->add('permissions', 'choice', array(
				'choices' => array(
					'r' => 'Read',
					'w' => 'Write',
					'a' => 'Anonymous'
				),
				'data' => array('r', 'w'),
				'multiple' => true,
				'expanded' => true,
				'required' => true
			))
			->getForm();

		$form->handleRequest($request);

		if ($form->isValid()) {
			$data = $form->getData();

			$app['boards']->create($data);

			$app['flashbag']->add('message', 'Board created');

			return $app->redirect($app['url_generator']->generate('admin.boards'));
		}

		$vars['board_form'] = $form->createView();

		return $app['twig']->render('@admin/boards/add.html', $vars);
	}

	public function edit(Request $request, Application $app, $id) {
		$vars['page_title'] = 'Edit Board';

		if (!$board = $app['boards']->byId($id)) {
			$app['flashbag']->add('message', 'Cannot find requested board');

			return $app->redirect($app['url_generator']->generate('admin.boards'));
		}

		$categories = array();

		foreach ($app['board_categories']->all() as $category) {
			$categories[$category['id']] = $category['name'];
		}

		$categories['0'] = 'Unassigned';

		foreach ($app['boards']->all() as $boards) {
			if ($boards['slug'] != $board['slug']) {
				$vars['extra_data']['existing_slugs'][] = $boards['slug'];
			}
		}

		$form = $app['form.factory']->createNamedBuilder(null, 'form')
			->add('name', 'text', array(
				'constraints' => array(
					new Assert\NotBlank(),
					new Assert\Length(array('min' => 5))
				),
				'data' => $board['name']
			))
			->add('slug', 'text', array(
				'constraints' => array(
					new Assert\NotBlank(),
					new CustomAssert\RecordExistence(array(
						'validate' => 'exists',
						'model' => new Boards(),
						'row' => 'slug',
						'exclude' => $board['slug'],
						'message' => 'Slug already exists'
					))
				),
				'data' => $board['slug']
			))
			->add('description', 'textarea', array(
				'constraints' => array(
					new Assert\NotBlank(),
					new Assert\Length(array('min' => 5))
				),
				'data' => $board['description']
			))
			->add('category', 'choice', array(
				'choices' => $categories,
				'data' => $board['category']
			))
			->add('permissions', 'choice', array(
				'choices' => array(
					'r' => 'Read',
					'w' => 'Write',
					'a' => 'Anonymous'
				),
				'data' => explode('/', $board['permissions']),
				'multiple' => true,
				'expanded' => true,
				'required' => true
			))
			->getForm();

		$form->handleRequest($request);

		if ($form->isValid()) {
			$data = $form->getData();
			$data['id'] = $id;

			$app['boards']->edit($data);

			$app['flashbag']->add('message', 'Board changes updated');

			return $app->redirect($app['url_generator']->generate('admin.boards'));
		}

		$vars['board'] = $board;
		$vars['board_form'] = $form->createView();

		return $app['twig']->render('@admin/boards/edit.html', $vars);
	}

	public function delete(Request $request, Application $app, $id) {
		$vars['page_title'] = 'Delete Board';

		if (!$board = $app['boards']->byId($id)) {
			$app['flashbag']->add('message', 'Cannot find requested board');

			return $app->redirect($app['url_generator']->generate('admin.boards'));
		}

		$actions = array();

		foreach ($app['boards']->all() as $boards) {
			$actions[$boards['id']] = 'Move threads to ' . $boards['name'];
		}

		$actions['0'] = 'Delete threads (this cannot be undone)';

		$form = $app['form.factory']->createNamedBuilder(null, 'form')
			->add('delete', 'hidden')
			->add('action', 'choice', array(
				'choices' => $actions,
				'label' => 'Choose what action to do with the threads in this board'
			))
			->getForm();

		$form->handleRequest($request);

		if ($form->isValid()) {
			$data = $form->getData();
			$data['id'] = $id;

			$app['boards']->delete($data);

			$app['flashbag']->add('message', 'Board deleted');

			return $app->redirect($app['url_generator']->generate('admin.boards'));
		}

		$vars['board'] = $board;
		$vars['board_form'] = $form->createView();

		return $app['twig']->render('@admin/boards/delete.html', $vars);
	}

}