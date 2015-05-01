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
use Symfony\Component\Validator\Constraints as Assert;
use EWZ\Bundle\RecaptchaBundle\Validator\Constraints as Recaptcha;
use EWZ\Bundle\RecaptchaBundle\Validator\Constraints\True as CaptchaTrue;
use Boardy\Constraints as CustomAssert;
use Boardy\Models\Users;
use Boardy\Models\UsersSession;
use Boardy\Utils\Helpers;

class AuthController {

	public function login(Request $request, Application $app) {
		$vars['page_title'] = 'Login';

		$form = $app['form.factory']->createNamedBuilder(null, 'form')
			->add('email', 'email')
			->add('password', 'password')
			->getForm();

		$form->handleRequest($request);

		if ($request->getMethod() == 'GET') {
			$app['flashbag']->set('destination', $request->query->get('to'));
		}

		if ($form->isValid()) {
			$data = $form->getData();

			if (!$user = $app['auth']->login($data)) {
				$app['flashbag']->add('message', 'Invalid email and/or password.');

				return $app->redirect($app['url_generator']->generate('auth.login'));
			}

			if ($user->acctype == 'UNVERIFIED') {
				return $app->redirect($app['url_generator']->generate('auth.verify'));
			}

			if ($destination = $app['flashbag']->get('destination')) {
				return $app->redirect($request->getSchemeAndHttpHost() . '/' . ltrim($destination[0], '/'));
			}

			return $app->redirect($app['url_generator']->generate('site.index'));
		}

		$vars['login_form'] = $form->createView();

		return $app['twig']->render('@theme/auth/login.html', $vars);
	}

	public function logout(Request $request, Application $app) {
		if ($app['auth']->getSession() == $request->query->get('_')) {
			$app['auth']->logout();
		}

		return $app->redirect($app['url_generator']->generate('site.index'));
	}

	public function register(Request $request, Application $app) {
		$vars['page_title'] = 'Register';

		$form = $app['form.factory']->createNamedBuilder(null, 'form')
			->add('name', 'text', array(
				'constraints' => array(
					new Assert\NotBlank(),
					new Assert\Length(array('min' => 5))
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
						'comparator' => '=',
						'message' => 'Username already exists'
					))
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
			));

		if ($app['configurations']->get('recaptcha_enable')) {
			$form->add('captcha', 'ewz_recaptcha', array(
				'constraints' => new CaptchaTrue(),
				'attr' => array(
					'options' => array(
						'theme' => 'clean'
					)
				)
			));
		}

		$form = $form->getForm();
		$form->handleRequest($request);

		if ($form->isValid()) {
			$data = $form->getData();

			$app['user']->create($data);

			return $app->redirect($app['url_generator']->generate('auth.verify'));
		}

		$vars['registration_form'] = $form->createView();

		return $app['twig']->render('@theme/auth/register.html', $vars);
	}

	public function verify(Request $request, Application $app) {
		$vars['page_title'] = 'Account verification';

		$form = $app['form.factory']->createNamedBuilder(null, 'form')
			->add('dummy', 'hidden', array(
				'data' => 'dummy'
			))
			->getForm();

		$form->handleRequest($request);

		if ($form->isValid()) {
			$app['flashbag']->add('message', 'The verification email has been sent.');
			$app['user']->sendVerification();

			return $app->redirect($app['url_generator']->generate('auth.verify'));
		}

		$vars['verify_form'] = $form->createView();

		return $app['twig']->render('@theme/auth/verify.html', $vars);
	}

	public function verification(Request $request, Application $app, $hash) {
		$vars['page_title'] = 'Account verification';
		$vars['error_message'] = '';

		if (!$app['user']->verify($hash)) {
			return $app['twig']->render('@theme/auth/verification_error.html', $vars);	
		}

		return $app->redirect($app['url_generator']->generate('auth.verified'));
	}

	public function verified(Request $request, Application $app) {
		$vars['page_title'] = 'Account verification';

		return $app['twig']->render('@theme/auth/verified.html', $vars);
	}

}