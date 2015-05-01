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


namespace Boardy\Utils;

class Helpers {

	public static function noise($size = 32, $pool = null) {
		if ($pool === null) {
			$pool = 'abcefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
		}

		return substr(str_shuffle(str_repeat($pool, 3)), 0, $size);
	}

	public static function truncateSlug($string, $limit = 50) {
		if (strlen($string) <= $limit) {
			return $string;
		}
		
		return substr($string, 0, strrpos(substr($string, 0, $limit), ' '));
	}

	public static function isOnline($last_activity, $active) {
		return (!$active || time() - strtotime($last_activity) > 600) === false;
	}

}