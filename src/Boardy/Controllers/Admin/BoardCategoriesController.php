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
use Boardy\Models\BoardCategories;

class BoardCategoriesController {

	public function index(Request $request, Application $app) {
		$vars['page_title'] = 'Categories';
		$vars['categories'] = $app['board_categories']->all();

		return $app['twig']->render('@admin/categories/index.html', $vars);
	}

	public function add(Request $request, Application $app) {
		$vars['page_title'] = 'Add Category';

		foreach ($app['board_categories']->all() as $categories) {
			$vars['extra_data']['existing_slugs'][] = $categories['slug'];
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
						'model' => new BoardCategories(),
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
			->getForm();

		$form->handleRequest($request);

		if ($form->isValid()) {
			$data = $form->getData();

			$app['board_categories']->create($data);

			$app['flashbag']->add('message', 'Category created');

			return $app->redirect($app['url_generator']->generate('admin.categories'));
		}

		$vars['category_form'] = $form->createView();

		return $app['twig']->render('@admin/categories/add.html', $vars);
	}

	public function edit(Request $request, Application $app, $id) {
		$vars['page_title'] = 'Edit Category';

		if (!$category = $app['board_categories']->byId($id)) {
			$app['flashbag']->add('message', 'Cannot find requested category');

			return $app->redirect($app['url_generator']->generate('admin.categories'));
		}

		foreach ($app['board_categories']->all() as $categories) {
			if ($categories['slug'] != $category['slug']) {
				$vars['extra_data']['existing_slugs'][] = $categories['slug'];
			}
		}

		$form = $app['form.factory']->createNamedBuilder(null, 'form')
			->add('name', 'text', array(
				'constraints' => array(
					new Assert\NotBlank(),
					new Assert\Length(array('min' => 5))
				),
				'data' => $category['name']
			))
			->add('slug', 'text', array(
				'constraints' => array(
					new Assert\NotBlank(),
					new CustomAssert\RecordExistence(array(
						'validate' => 'exists',
						'model' => new BoardCategories(),
						'row' => 'slug',
						'exclude' => $category['slug'],
						'message' => 'Slug already exists'
					))
				),
				'data' => $category['slug']
			))
			->add('description', 'textarea', array(
				'constraints' => array(
					new Assert\NotBlank(),
					new Assert\Length(array('min' => 5))
				),
				'data' => $category['description']
			))
			->getForm();

		$form->handleRequest($request);

		if ($form->isValid()) {
			$data = $form->getData();
			$data['id'] = $category['id'];

			$app['board_categories']->edit($data);

			$app['flashbag']->add('message', 'Category changes saved');

			return $app->redirect($app['url_generator']->generate('admin.categories'));
		}

		$vars['category'] = $category;
		$vars['category_form'] = $form->createView();

		return $app['twig']->render('@admin/categories/edit.html', $vars);
	}

	public function delete(Request $request, Application $app, $id) {
		$vars['page_title'] = 'Delete Category';

		if (!$category = $app['board_categories']->byId($id)) {
			$app['flashbag']->add('message', 'Cannot find requested category');

			return $app->redirect($app['url_generator']->generate('admin.categories'));
		}

		$form = $app['form.factory']->createNamedBuilder(null, 'form')
			->add('delete', 'hidden')
			->getForm();

		$form->handleRequest($request);

		if ($form->isValid()) {
			$data = $form->getData();
			
			$app['board_categories']->delete($id);

			$app['flashbag']->add('message', 'Category deleted');

			return $app->redirect($app['url_generator']->generate('admin.categories'));
		}

		$vars['category'] = $category;
		$vars['boards'] = $app['boards']->byCategory($category['id']);
		$vars['category_form'] = $form->createView();

		return $app['twig']->render('@admin/categories/delete.html', $vars);
	}

}