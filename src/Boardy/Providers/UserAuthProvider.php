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
use Boardy\Models\Users;
use Boardy\Models\UsersSession;
use Boardy\Models\Configurations;
use Boardy\Utils\Hash;
use Boardy\Utils\Helpers;

class UserAuthProvider implements ServiceProviderInterface {

	protected $app;

	public function register(Application $app) {
		$this->app = $app;

		$app['auth'] = $this;
		$app['current_user'] = $this->getCurrentUser();
	}

	public function boot(Application $app) {

	}

	public function login($data) {
		if (!$user = Users::where('email', '=', $data['email'])->get()->first()) {
			return false;
		}

		if (!Hash::check($data['password'], $user->password)) {
			return false;
		}

		$datetime = date('Y-m-d H:i:s');

		$user->active = '1';
		$user->last_activity = $datetime;
		$user->last_login = $datetime;
		$user->ip = $this->app['request_stack']->getMasterRequest()->getClientIp();
		$user->save();

		$this->newSession($user);

		return $user;
	}

	public function logout() {
		$user = $this->getCurrentUser(true);

		$user->active = '0';
		$user->save();

		$this->clearSession();
	}

	public function getCurrentUser($raw = false) {
		if (!$hash = $this->getSession()) {
			return false;
		}

		if (!$session = UsersSession::find($hash)) {
			return false;
		}

		if (!$user = Users::find($session->user)) {
			return false;
		}

		$this->updateSession();

		$user->active = '1';
		$user->last_activity = date('Y-m-d H:i:s');
		$user->save();

		if ($raw) {
			return $user;
		}

		return $user->getAttributes();
	}

	public function newSession($user) {
		$session_id = hash('sha1', $user->username . Helpers::noise());
		$session = new UsersSession();

		$session->user = $user->id;
		$session->hash = $session_id;
		$session->ip = $this->app['request_stack']->getMasterRequest()->getClientIp();
		$session->ua = $this->app['request_stack']->getMasterRequest()->headers->get('user-agent');
		$session->last_activity = date('Y-m-d H:i:s');

		$session->save();
		$this->app['session']->set('auth', $session_id);

		return $session_id;
	}

	public function getSession() {
		return $this->app['session']->get('auth');
	}

	public function updateSession() {
		if (!$hash = $this->getSession()) {
			return false;
		}

		$session = UsersSession::where('hash', '=', $hash)->get()->first();
		$session->last_activity = date('Y-m-d H:i:s');

		$session->save();
	}

	public function clearSession() {
		UsersSession::find($this->getSession())->delete();
		$this->app['session']->remove('auth');
	}

}