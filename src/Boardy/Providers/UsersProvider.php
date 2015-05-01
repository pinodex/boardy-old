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

class UsersProvider implements ServiceProviderInterface {

	protected $app, $user;

	private $acctypes = array('USER', 'UNVERIFIED', 'ADMIN');

	public function register(Application $app) {
		$this->app = $app;
		$app['user'] = $this;
	}

	public function boot(Application $app) {

	}

	public function create($data) {
		$user = new Users();

		$user->name = $data['name'];
		$user->email = $data['email'];
		$user->username = preg_replace('/\s+/', '', $data['username']);
		$user->password = Hash::make($data['password']);
		$user->registered = date('Y-m-d H:i:s');
		$user->verification_key = Helpers::noise(8, hash('sha1', $data['username']));
		$user->save();

		$this->user = $user;
		$this->app['session']->set('auth', $this->app['auth']->newSession($user));

		if ($this->app['configurations']->get('verify_email')) {
			$this->sendVerification();
		}

		return $user;
	}

	public function createAdmin($data) {
		if (!in_array($data['acctype'], $this->acctypes)) {
			$data['acctype'] = 'USER';
		}

		$user = new Users();

		$user->name = $data['name'];
		$user->email = $data['email'];
		$user->username = preg_replace('/\s+/', '', $data['username']);
		$user->password = Hash::make($data['password']);
		$user->acctype = $data['acctype'];
		$user->registered = date('Y-m-d H:i:s');
		$user->verification_key = '';
		$user->save();

		return $user;
	}

	public function edit($data) {
		if (!$user = Users::find($data['id'])) {
			return false;
		}

		$exceptions = array('id', 'active', 'last_activity', 'last_login', 'registered');

		foreach ($data as $key => $value) {
			if (isset($exceptions[$key])) {
				continue;
			}

			if ($key == 'username') {
				$value = preg_replace('/\s+/', '', $value);
			}

			if ($key == 'password') {
				$value = Hash::make($value);
			}

			$user->{$key} = $value;
		}

		$user->save();

		return $user;
	}

	public function delete($id) {
		if ($user = Users::find($id)) {
			return $user->delete();
		}

		return false;
	}

	public function verify($hash) {
		if (!$user = Users::where('verification_key', '=', $hash)->get()->first()) {
			return false;
		}

		$user->acctype = 'USER';
		$user->verification_key = '';
		$user->save();

		return true;
	}

	public function byId($id) {
		return $this->fetch('id', '=', $id, false);
	}

	public function all() {
		$all = array();

		foreach (Users::all() as $user) {
			if (Helpers::isOnline($user->last_activity, $user->active)) {
				$user->active = '1';
			} else {
				$user->active = '0';
			}

			$all[] = $user->getAttributes();
		}

		return $all;
	}

	public function fetch($column, $comparator, $value, $unknown = true) {
		$user = Users::where($column, $comparator, $value)->get()->first();

		if (Helpers::isOnline($user->last_activity, $user->active)) {
			$user->active = '1';
		} else {
			$user->active = '0';
		}

		if (!$user && !$unknown) {
			return false;
		}

		if (!$user) {
			return array(
				'id' => '0',
				'name' => 'Unknown User',
				'email' => null,
				'username' => 'null',
				'acctype' => 'UNREGISTERED',
				'active' => '0',
				'last_activity' => '0000-00-00 00:00:00',
				'last_login' => '0000-00-00 00:00:00',
				'registered' => '0000-00-00 00:00:00'
			);
		}

		return $user->getAttributes();
	}

	public function sendVerification() {
		if (!$this->user) {
			return false;
		}

		$message = \Swift_Message::newInstance()
			->setSubject($this->app['forum_name'] . ' Account Verification')
			->setFrom(array(
				$this->app['sent_from_address'] => $this->app['forum_name']
			))
			->setTo(array(
				$this->user->email => $this->user->name
			))
			->setBody($this->app['twig']->render('emails/account_verification.html', array(
				'name' => $this->user->name,
				'verification_key' => $this->user->verification_key,
				'recipient' => $this->user->email
			)))
			->setContentType("text/html");

		$this->app['mailer']->send($message);
	}

	public function hasPermission($action, $permissions) {
		$permissions = explode('/', $permissions);

		if (!$this->user && in_array('a', $permissions)) {
			return true;
		}

		if ($this->user) {
			if ($this->user->acctype == 'ADMIN' || $this->user->acctype == 'MODERATOR') {
				return true;
			}

			if ($this->user->acctype == 'UNVERIFIED' && $action == 'read') {
				return true;
			}

			if ($action == 'read' && in_array('r', $permissions)) {
				return true;
			}

			if ($action == 'write' && in_array('w', $permissions)) {
				return true;
			}
		}

		return false;
	}

}