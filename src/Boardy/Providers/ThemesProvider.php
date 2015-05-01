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

class ThemesProvider implements ServiceProviderInterface {

	protected $app;

	private $dirs;

	public function register(Application $app) {
		$this->app = $app;
		$app['themes'] = $this;
	}

	public function boot(Application $app) {
		
	}

	public function load() {
		$iterator = new \DirectoryIterator(APP . 'themes/');
		
		foreach ($iterator as $directory) {
			if ($directory->isDir() && !$directory->isDot()) {
				$this->dirs[$directory->getBaseName()] = $directory->getRealPath() . DS;
			}
		}
	}

	public function getManifest($theme = null) {
		if (!$this->dirs) {
			$this->load();
		}

		if (!$theme) {
			$theme = $this->app['configurations']->get('theme');
		}

		if (@$manifest = file_get_contents($this->dirs[$theme] . 'manifest.json')) {
			return @json_decode($manifest);
		}

		return false;
	}

	public function getList() {
		if (!$this->dirs) {
			$this->load();
		}

		$themes = array();

		foreach ($this->dirs as $i => $dir) {
			if (@$manifest = file_get_contents($dir . 'manifest.json')) {
				if (@$manifest = json_decode($manifest)) {
					$themes[$i] = $manifest->name . ' by ' . $manifest->author . ' [' . $i . ']';
				}
			}
		}

		return $themes;
	}

}