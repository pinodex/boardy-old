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
use Boardy\Models\Users;

class UsersController {

	public function index(Request $request, Application $app) {
		$vars['page_title'] = 'Users';
		$vars['users'] = $app['user']->all();

		return $app['twig']->render('@admin/users/index.html', $vars);
	}

	public function add(Request $request, Application $app) {
		$vars['page_title'] = 'Add User';
		
		$form = $app['form.factory']->createNamedBuilder(null, 'form')
			->add('name', 'text', array(
				'constraints' => array(
					new Assert\NotBlank(),
					new Assert\Length(array('min' => 2))
				)
			))
			->add('email', 'email', array(
				'constraints' => array(
					new Assert\Email(),
					new CustomAssert\RecordExistence(array(
						'validate' => 'exists',
						'model' => new Users(),
						'row' => 'email',
						'message' => 'Email already exists'
					))
				)
			))
			->add('username', 'text', array(
				'constraints' => array(
					new Assert\NotBlank(),
					new Assert\Length(array('min' => 2)),
					new CustomAssert\RecordExistence(array(
						'validate' => 'exists',
						'model' => new Users(),
						'row' => 'username',
						'message' => 'Username already exists'
					))
				)
			))
			->add('acctype', 'choice', array(
				'choices' => array(
					'USER' => 'User',
					'UNVERIFIED' => 'Unverified',
					'ADMIN' => 'Administrator'
				)
			))
			->add('password', 'repeated', array(
				'type' => 'password',
				'constraints' => array(
					new Assert\NotBlank(),
					new Assert\Length(array('min' => 8))
				),
				'required' => true,
				'first_options' => array(
					'label' => 'Password'
				),
				'second_options' => array(
					'label' => 'Repeat Password'
				)
			))
			->getForm();

		$form->handleRequest($request);

		if ($form->isValid()) {
			$data = $form->getData();

			$app['user']->createAdmin($data);

			$app['flashbag']->add('message', 'User created');

			return $app->redirect($app['url_generator']->generate('admin.users'));
		}

		$vars['user_form'] = $form->createView();

		return $app['twig']->render('@admin/users/add.html', $vars);
	}

	public function edit(Request $request, Application $app, $id) {
		$vars['page_title'] = 'Edit User';

		if (!$user = $app['user']->byId($id)) {
			$app['flashbag']->add('message', 'Cannot find requested user');

			return $app->redirect($app['url_generator']->generate('admin.users'));
		}
		
		$form = $app['form.factory']->createNamedBuilder(null, 'form')
			->add('name', 'text', array(
				'constraints' => array(
					new Assert\NotBlank(),
					new Assert\Length(array('min' => 5))
				),
				'data' => $user['name']
			))
			->add('email', 'email', array(
				'constraints' => array(
					new Assert\Email(),
					new CustomAssert\RecordExistence(array(
						'validate' => 'exists',
						'model' => new Users(),
						'row' => 'email',
						'exclude' => $user['email'],
						'message' => 'Email already exists'
					))
				),
				'data' => $user['email']
			))
			->add('username', 'text', array(
				'constraints' => array(
					new Assert\NotBlank(),
					new Assert\Length(array('min' => 2)),
					new CustomAssert\RecordExistence(array(
						'validate' => 'exists',
						'model' => new Users(),
						'row' => 'username',
						'exclude' => $user['username'],
						'message' => 'Username already exists'
					))
				),
				'data' => $user['username']
			))
			->add('acctype', 'choice', array(
				'choices' => array(
					'USER' => 'User',
					'UNVERIFIED' => 'Unverified',
					'ADMIN' => 'Administrator'
				),
				'data' => $user['acctype']
			))
			->add('password', 'repeated', array(
				'type' => 'password',
				'constraints' => array(
					new Assert\Length(array('min' => 8))
				),
				'required' => false,
				'first_options' => array(
					'label' => 'Password (leave empty if not changing)'
				),
				'second_options' => array(
					'label' => 'Repeat Password (leave empty if not changing)'
				)
			))
			->getForm();

		$form->handleRequest($request);

		if ($form->isValid()) {
			$data = $form->getData();
			$data['id'] = $id;

			if (!$data['password'] || empty($data['password'])) {
				unset($data['password']);
			}

			$app['user']->edit($data);

			$app['flashbag']->add('message', 'User changes updated');

			return $app->redirect($app['url_generator']->generate('admin.users'));
		}

		$vars['user_form'] = $form->createView();

		return $app['twig']->render('@admin/users/edit.html', $vars);
	}

	public function delete(Request $request, Application $app, $id) {
		$vars['page_title'] = 'Delete User';

		if (!$user = $app['user']->byId($id)) {
			$app['flashbag']->add('message', 'Cannot find requested user');

			return $app->redirect($app['url_generator']->generate('admin.users'));
		}
		
		$form = $app['form.factory']->createNamedBuilder(null, 'form')
			->add('delete', 'hidden')
			->getForm();

		$form->handleRequest($request);

		if ($form->isValid()) {
			$data = $form->getData();
			
			$app['user']->delete($id);

			$app['flashbag']->add('message', 'User deleted');

			return $app->redirect($app['url_generator']->generate('admin.users'));
		}

		$vars['user'] = $user;
		$vars['totals'] = array(
			'posts' => count($app['posts']->byUser($user['id'])),
			'replies' => count($app['replies']->byUser($user['id']))
		);
		$vars['user_form'] = $form->createView();

		return $app['twig']->render('@admin/users/delete.html', $vars);
	}

}