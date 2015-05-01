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

class AppVersionProvider implements ServiceProviderInterface {

	private $info;

	public function register(Application $app) {
		$app['version'] = $this;
	}

	public function boot(Application $app) {
		
	}

	public function __construct() {
		if (!$file = @file_get_contents(APP . 'version.json')) {
			throw new Exception('Cannot read version information file', 1);
			return;
		}

		if (!$file = @json_decode($file)) {
			throw new Exception('Version information file is not a JSON', 1);
			return;
		}

		$this->info = $file;
	}

	public function full() {
		return $this->info->major . '.' . $this->info->minor . '.' . $this->info->release;
	}

	public function info() {
		return 'Boardy ' . $this->full() . '-' . $this->info->tag;
	}

	public function major() {
		return $this->info->major;
	}

	public function minor() {
		return $this->info->minor;
	}

	public function release() {
		return $this->info->release;
	}

	public function tag() {
		return $this->info->tag;
	}

}