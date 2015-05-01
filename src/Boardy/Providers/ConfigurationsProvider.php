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
use Boardy\Models\Configurations;

class ConfigurationsProvider implements ServiceProviderInterface {

	protected $app;

	private $configurations;

	public function register(Application $app) {
		$app['configurations'] = $this;

		$configurations = Configurations::getAllAttributes();

		foreach ($configurations as $configuration) {
			if ($configuration['type'] == 'boolean') {
				$configuration['value'] = filter_var($configuration['value'], FILTER_VALIDATE_BOOLEAN);
			}

			if ($configuration['type'] == 'integer') {
				$configuration['value'] = (int) $configuration['value'];
			}

			$this->configurations[$configuration['id']] = $configuration['value'];
		}
	}

	public function boot(Application $app) {
		
	}

	public function get($id, $fallback = null) {
		return $this->configurations[$id];
	}

	public function set($id, $value, $data = null) {
		return Configurations::set($id, $value, $data);
	}

	public function setBatch($data) {
		$configurations = Configurations::all();

		foreach ($configurations as $i => $configuration) {
			if (isset($data[$configuration->id])) {
				$configuration->value = $data[$configuration->id];
				$configuration->save();
			}
		}

		return $configurations;
	}

}