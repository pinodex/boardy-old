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


namespace Boardy\Models;

use Illuminate\Database\Eloquent\Model;

class Configurations extends Model {

	protected $table = 'configurations';

	public $timestamps = false;

	private static $locks = array(
		'forum_name',
		'posts_per_page',
		'recaptcha_enable',
		'recaptcha_private',
		'recaptcha_public',
		'replies_per_page',
		'theme',
		'verify_email'
	);

	public static function get($id, $fallback = null) {
		if ($meta = static::find($id)) {
			if ($meta->type == 'boolean') {
				return filter_var($meta->value, FILTER_VALIDATE_BOOLEAN);
			}

			if ($meta->type == 'integer') {
				return (int) $meta->value;
			}

			return $meta->value;
		}

		return $fallback;
	}

	public static function set($id, $value, $data = null) {
		$values = array(
			'id' => $id,
			'value' => $value
		);

		if (isset($data['description'])) {
			$values['description'] = $data['description'];
		}

		if (isset($data['type'])) {
			$values['type'] = $data['type'];
		}

		if (!isset($values['type'])) {
			$values['type'] = gettype($value);
		}

		if (!$meta = static::find($id)) {
			return static::create($values);
		}

		return static::find($id)->update($values);
	}

	public static function getAllAttributes() {
		$configurations = static::orderBy('ordering', 'ASC')->get();

		foreach ($configurations as $i => $configuration) {
			$configurations[$i]['locked'] = false;

			if (in_array($configuration->id, static::$locks)) {
				$configurations[$i]['locked'] = true;
			}

			$configurations[$i] = $configuration->getAttributes();
		}

		return $configurations;
	}

}