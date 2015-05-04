<?php

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Illuminate\Database\Capsule\Manager as Capsule;
use Boardy\Utils\Hash;

$app = new Application();
$app['debug'] = true;

if (file_exists(BOARDY . '/config.php')) {
	return $app;
}

$capsule = new Capsule();

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => ROOT . '/views',
));
$app->register(new Silex\Provider\SessionServiceProvider());
$app->register(new Silex\Provider\FormServiceProvider());
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new Silex\Provider\FormServiceProvider());
$app->register(new Silex\Provider\ValidatorServiceProvider());
$app->register(new Silex\Provider\TranslationServiceProvider(), array(
    'translator.messages' => array(),
));

$app['flashbag'] = $app->share(function () use ($app) {
	return $app['session']->getFlashBag();
});

$app['twig'] = $app->extend('twig', function ($twig, $app) {
	
	$twig->addFunction(new \Twig_SimpleFunction('asset', function ($file) use ($app) {
		return $app['request_stack']->getMasterRequest()->getBasepath() . '/assets' . $file;
	}));

	$twig->addFunction(new \Twig_SimpleFunction('flashbag', function ($name) use ($app) {
		return $app['flashbag']->get($name);
	}));

	return $twig;
});

$capsule->setAsGlobal();

if ($connection = $app['session']->get('database_connection')) {
	$capsule->addConnection($connection);
}

$app->get('/', function () use ($app) {
	if (strpos($app['request']->getRequestUri(), 'index.php') === false) {
		return $app->redirect($app['request']->getBaseURL() . '/index.php');
	}

    return $app['twig']->render('index.html', array(
		'hide_header' => 1
	));
});

$app->match('/start', function (Request $request) use ($app) {
	$vars['page_title'] = 'Database';
	$vars['action_name'] = 'Configure Database';
	$vars['submit_label'] = 'Connect';

	$form = $app['form.factory']->createNamedBuilder(null, 'form')
		->add('driver', 'choice', array(
			'choices' => array(
				'mysql' => 'MySQL',
				'pgsql' => 'PostgreSQL'
			)
		))
		->add('host', 'text', array(
			'constraints' => array(
				new Assert\NotBlank()
			),
			'data' => 'localhost',
		))
		->add('username', 'text', array(
			'constraints' => array(
				new Assert\NotBlank()
			),
			'data' => 'root'
		))
		->add('password', 'password', array(
			'required' => false,
			'data' => ''
		))
		->add('database', 'text', array(
			'constraints' => array(
				new Assert\NotBlank()
			),
			'data' => 'boardy'
		))
		->add('prefix', 'text', array(
			'required' => false,
			'data' => 'boardy_'
		))
		->getForm();

	$form->handleRequest($request);

	if ($form->isValid()) {
		$data = $form->getData();
		$data['charset'] = 'utf8';
		$data['collation'] = 'utf8_unicode_ci';

		if (!$data['password']) {
			$data['password'] = '';
		}

		$error = false;

		try {
			new PDO($data['driver'] . ':host=' . $data['host'] . ';dbname=' . $data['database'],
				$data['username'], $data['password']);
		} catch (Exception $e) {
			$app['flashbag']->add('message', $e->getMessage());
			$error = true;
		}

		if (!$error) {
			$app['session']->set('database_connection', $data);
			return $app->redirect($app['url_generator']->generate('configure'));
		}
	}

	$vars['form'] = $form->createView();

    return $app['twig']->render('default.html', $vars);
})->bind('start');

$app->match('/configure', function (Request $request) use ($app) {
	$vars['page_title'] = 'Website';
	$vars['action_name'] = 'Configure Website';
	$vars['submit_label'] = 'Save';

	$timezones = DateTimeZone::listIdentifiers();

	$form = $app['form.factory']->createNamedBuilder(null, 'form')
		->add('site_name', 'text', array(
			'constraints' => array(
				new Assert\NotBlank()
			)
		))
		->add('timezone', 'choice', array(
			'choices' => $timezones,
			'data' => 416
		))
		->getForm();

	$form->handleRequest($request);

	if ($form->isValid()) {
		$data = $form->getData();
		
		$app['session']->set('site_info', $data);

		return $app->redirect($app['url_generator']->generate('admin'));
	}

	$vars['form'] = $form->createView();

    return $app['twig']->render('default.html', $vars);
})->before(function() use ($app) {
	if (!$app['session']->get('database_connection')) {
		return $app->redirect($app['url_generator']->generate('start'));
	}
})->bind('configure');

$app->match('/admin', function (Request $request) use ($app) {
	$vars['page_title'] = 'Admin';
	$vars['action_name'] = 'Configure Admin';
	$vars['submit_label'] = 'Save';

	$form = $app['form.factory']->createNamedBuilder(null, 'form')
		->add('name', 'text', array(
			'constraints' => array(
				new Assert\NotBlank(),
				new Assert\Length(array('min' => 2))
			)
		))
		->add('email', 'text', array(
			'constraints' => array(
				new Assert\NotBlank()
			)
		))
		->add('username', 'text', array(
			'constraints' => array(
				new Assert\NotBlank(),
				new Assert\Length(array('min' => 2)),
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
		
		$data['password'] = Hash::make($data['password']);
		$data['acctype'] = 'ADMIN';
		$data['registered'] = date('Y-m-d H:i:s');

		$app['session']->set('admin_account', $data);

		return $app->redirect($app['url_generator']->generate('post'));
	}

	$vars['form'] = $form->createView();

    return $app['twig']->render('default.html', $vars);
})->before(function() use ($app) {
	if (!$app['session']->get('database_connection')) {
		return $app->redirect($app['url_generator']->generate('start'));
	}

	if (!$app['session']->get('site_info')) {
		return $app->redirect($app['url_generator']->generate('configure'));
	}
})->bind('admin');

$app->get('/post', function (Request $request) use ($app) {
	$vars['page_title'] = 'Post Install';

	if ($app['session']->get('installed')) {
		return $app['twig']->render('post.html', $vars);
	}

    return $app['twig']->render('post.html', $vars);
})->before(function() use ($app) {
	if (!$app['session']->get('database_connection')) {
		return $app->redirect($app['url_generator']->generate('start'));
	}

	if (!$app['session']->get('site_info')) {
		return $app->redirect($app['url_generator']->generate('configure'));
	}

	if (!$app['session']->get('admin_account')) {
		return $app->redirect($app['url_generator']->generate('admin'));
	}
})->bind('post');

$app->get('/post/async', function (Request $request) use ($app) {
	if ($app['session']->get('installed')) {
		return $app->json(array(
			'status' => 'ok'
		));
	}

	$database = $app['session']->get('database_connection');
	$site = $app['session']->get('site_info');
	$admin = $app['session']->get('admin_account');

	$timezones = DateTimeZone::listIdentifiers();

	Capsule::schema()->create('boards', function($table) {
		$table->increments('id');
		$table->string('name');
		$table->string('description');
		$table->string('slug');
		$table->integer('category')->default(0);
		$table->string('permissions', 8)->default('r/w');
	});

	Capsule::schema()->create('board_categories', function($table) {
		$table->increments('id');
		$table->string('name');
		$table->string('description');
		$table->string('slug');
	});

	Capsule::schema()->create('configurations', function($table) {
		$table->string('id');
		$table->string('value');
		$table->string('type', 8)->default('string');
		$table->string('description')->nullable();
		$table->integer('ordering')->nullable();
		$table->primary('id');
	});

	Capsule::schema()->create('posts', function($table) {
		$table->increments('id');
		$table->string('name');
		$table->string('slug');
		$table->text('content');
		$table->string('tags')->nullable();
		$table->integer('board')->default(0);
		$table->timestamp('created')->nullable();
		$table->timestamp('last_edited')->nullable();
		$table->softDeletes();
		$table->integer('author')->default(0);
		$table->string('ip', 64);
	});

	Capsule::schema()->create('replies', function($table) {
		$table->increments('id');
		$table->integer('post')->nullable();
		$table->text('content');
		$table->timestamp('created')->nullable();
		$table->timestamp('last_edited')->nullable();
		$table->softDeletes();
		$table->integer('author')->default(0);
		$table->string('ip', 64);
	});

	Capsule::schema()->create('sessions', function($table) {
		$table->string('id');
		$table->binary('data');
		$table->integer('time');
		$table->mediumInteger('lifetime');
		$table->primary('id');
	});

	Capsule::schema()->create('users', function($table) {
		$table->increments('id');
		$table->string('name');
		$table->string('email');
		$table->string('username');
		$table->string('password');
		$table->string('acctype', 16)->default('UNVERIFIED');
		$table->tinyInteger('active')->default(0);
		$table->timestamp('last_activity')->nullable();
		$table->timestamp('last_login')->nullable();
		$table->timestamp('registered')->nullable();
		$table->softDeletes();
		$table->string('ip', 64)->default('0.0.0.0');
		$table->string('verification_key', 10)->default('');
	});

	Capsule::schema()->create('users_sessions', function($table) {
		$table->increments('id');
		$table->integer('user');
		$table->string('hash', 40);
		$table->string('ip', 64);
		$table->text('ua');
		$table->timestamp('last_activity')->nullable();
	});

	Capsule::table('configurations')->insert(array(
		array(
			'id' => 'forum_name',
			'value' => $site['site_name'],
			'type' => 'string',
			'description' => 'Forum Name',
			'ordering' => '1'
		),
		array(
			'id' => 'posts_per_page',
			'value' => '20',
			'type' => 'integer',
			'description' => 'Posts Per Page',
			'ordering' => '2'
		),
		array(
			'id' => 'replies_per_page',
			'value' => '20',
			'type' => 'integer',
			'description' => 'Replies Per Page',
			'ordering' => '3'
		),
		array(
			'id' => 'recaptcha_enable',
			'value' => 'false',
			'type' => 'boolean',
			'description' => 'Enable Recaptcha',
			'ordering' => '4'
		),
		array(
			'id' => 'recaptcha_public',
			'value' => '',
			'type' => 'string',
			'description' => 'Recaptcha Public Key',
			'ordering' => '5'
		),
		array(
			'id' => 'recaptcha_private',
			'value' => '',
			'type' => 'string',
			'description' => 'Recaptcha Private Key',
			'ordering' => '6'
		),
		array(
			'id' => 'theme',
			'value' => 'default',
			'type' => 'string',
			'description' => 'Theme',
			'ordering' => '7'
		),
		array(
			'id' => 'verify_email',
			'value' => 'false',
			'type' => 'boolean',
			'description' => 'Require Email Verification',
			'ordering' => '8'
		),
	));

	Capsule::table('users')->insert($admin);

	Capsule::table('board_categories')->insert(array(
		'name' => 'Information',
		'description' => 'All about this website',
		'slug' => 'information'
	));

	Capsule::table('boards')->insert(array(
		'name' => 'News and Updates',
		'description' => 'Every new stuffs in this website are posted here.',
		'slug' => 'news-and-updates',
		'category' => '1',
		'permissions' => 'r/w/a'
	));

	Capsule::table('posts')->insert(array(
		'name' => 'Welcome to ' . $site['site_name'] . '!',
		'slug' => 'welcome-humans',
		'content' => file_get_contents(ROOT . '/sample_post.txt'),
		'tags' => 'introduction,welcome',
		'board' => '1',
		'created' => date('Y-m-d H:i:s'),
		'author' => '1',
		'ip' => '127.0.0.1'
	));

	Capsule::table('replies')->insert(array(
		'post' => '1',
		'content' => 'This is a sample reply. Every content here works with Markdown.',
		'created' => date('Y-m-d H:i:s'),
		'author' => '1',
		'ip' => '127.0.0.1'
	));

	$config = file_get_contents(ROOT . '/config.template.txt');
	$config = str_replace(
			array(
				'{timezone}',
				'{driver}',
				'{host}',
				'{database}',
				'{username}',
				'{password}',
				'{prefix}',
				'{charset}',
				'{collation}'
			),
			array(
				$timezones[$site['timezone']],
				$database['driver'],
				$database['host'],
				$database['database'],
				$database['username'],
				$database['password'],
				$database['prefix'],
				$database['charset'],
				$database['collation']
			),
		$config);
	file_put_contents(BOARDY . '/config.php', $config);

	$app['session']->set('installed', true);

    return $app->json(array(
		'status' => 'ok'
	));
})->before(function() use ($app) {
	if (!$app['session']->get('database_connection')) {
		return $app->json(array());
	}

	if (!$app['session']->get('site_info')) {
		return $app->json(array());
	}

	if (!$app['session']->get('admin_account')) {
		return $app->json(array());
	}
})->bind('post.async');

return $app;