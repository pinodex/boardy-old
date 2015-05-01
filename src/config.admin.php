<?php

/**
 * Boardy
 *
 * Simple PHP forum app.
 *
 * @package  boardy
 * @author   Raphael Marco <pinodex@outlook.ph>
 * @link     http://pinodex.io
 *
 * This file contains the admin panel specific
 * configuration. Only change this when necessary.
 */

$app['admin_side_bar'] = array(
	'Dashboard' => array(
		'icon' => 'fa fa-tachometer',
		'route' => 'admin.index'
	),
	'Categories' => array(
		'icon' => 'fa fa-folder',
		'route' => 'admin.categories'
	),
	'Boards' => array(
		'icon' => 'fa fa-file',
		'route' => 'admin.boards'
	),
	'Users' => array(
		'icon' => 'fa fa-users',
		'route' => 'admin.users'
	),
	'Configurations' => array(
		'icon' => 'fa fa-cogs',
		'route' => 'admin.configurations'
	)
);