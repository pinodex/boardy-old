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

class Hash {

	public static function make($value, $rounds = 10) {
		if ($rounds > 31 || $rounds < 4) {
			throw new OutOfRangeException('Blowfish iteration count must be between 4 and 31');
		}

		$rounds = sprintf('%02d', $rounds);

		$pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$salt = substr(str_shuffle(str_repeat($pool, 5)), 0, 22);

		return crypt($value, '$2a$' . $rounds . '$' . $salt);
	}
	
	public static function check($value, $hash) {
		return crypt($value, $hash) === $hash;
	}
	
}