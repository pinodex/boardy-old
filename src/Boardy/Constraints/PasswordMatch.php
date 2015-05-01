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

namespace Boardy\Constraints;

use Symfony\Component\Validator\Constraint;

class PasswordMatch extends Constraint {

	public $to;

	public $message = 'Password does not match';

}