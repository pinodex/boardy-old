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
use Boardy\Constraints as CustomAssert;
use Boardy\Utils\Hash;

class SettingsController {

	public static function account(Request $request, Application $app) {
		$user = $app['current_user'];

		$form = $app['form.factory']->createNamedBuilder(null, 'form')
			->add('name', 'text', array(
				'constraints' => array(
					new Assert\NotBlank(),
					new Assert\Length(array('min' => 5))
				),
				'data' => $user['name']
			))
			->add('username', 'text', array(
				'constraints' => array(
					new Assert\NotBlank(),
					new Assert\Length(array('min' => 2))
				),
				'data' => $user['username']
			))
			->add('email', 'email', array(
				'constraints' => array(
					new Assert\Email()
				),
				'data' => $user['email']
			))
			->add('old_password', 'password', array(
				'constraints' => array(
					new Assert\Length(array('min' => 8)),
					new CustomAssert\PasswordMatch(array(
						'to' => $app['current_user']['password']
					))
				),
				'required' => false,
				'label' => 'Old password (leave empty if not changing)'
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
					'label' => 'Repeat Password'
				)
			))
			->getForm();

		$form->handleRequest($request);

		if ($form->isValid()) {
			$data = $form->getData();
			$data['id'] = $user['id'];

			if ($data['old_password'] == null || $data['password'] == null) {
				unset($data['password']);
			}

			unset($data['old_password']);

			$app['user']->edit($data);
			$app['flashbag']->add('message', 'Account settings updated');

			if (isset($data['password'])) {
				$app['flashbag']->add('message', 'Password updated');
			}

			return $app->redirect($app['url_generator']->generate('user.view', array(
				'id' => $user['id'],
				'username' => $user['username']
			)));
		}

		$vars['page_title'] = 'Edit profile';
		$vars['user'] = $user;
		$vars['user_form'] = $form->createView();

		return $app['twig']->render('@theme/settings/account.html', $vars);
	}

}