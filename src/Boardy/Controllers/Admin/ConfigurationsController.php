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
use Boardy\Models\Configurations;

class ConfigurationsController {

	public function index(Request $request, Application $app) {
		$vars['page_title'] = 'Configurations';

		$configurations = Configurations::getAllAttributes();
		
		$form = $app['form.factory']->createNamedBuilder(null, 'form');

		foreach ($configurations as $i => $configuration) {
			$type = 'text';
			$label = $configuration['id'];

			if ($configuration['description']) {
				$label = $configuration['description'];
			}

			$options = array(
				'label' => $label,
				'data' => $configuration['value']
			);

			if ($configuration['type'] == 'integer') {
				$type = 'integer';
			}

			if ($configuration['type'] == 'boolean') {
				$type = 'choice';

				$options['choices'] = array(
					'true' => 'Yes',
					'false' => 'No'
				);
			}

			if ($configuration['id'] == 'theme') {
				$type = 'choice';

				$options['choices'] = $app['themes']->getList();
				$options['data'] = $configuration['value'];
			}

			$form->add($configuration['id'], $type, $options);
		}
			
		$form = $form->getForm();
		$form->handleRequest($request);

		if ($form->isValid()) {
			$data = $form->getData();

			$app['configurations']->setBatch($data);

			$app['flashbag']->add('message', 'Configuration changes saved');

			return $app->redirect($app['url_generator']->generate('admin.configurations'));
		}

		$vars['theme'] = $app['themes']->getManifest();
		$vars['configurations_form'] = $form->createView();

		return $app['twig']->render('@admin/configurations/index.html', $vars);
	}

}