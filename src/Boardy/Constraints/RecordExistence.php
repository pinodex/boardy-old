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

class RecordExistence extends Constraint {

	public $validate;

	public $model;

	public $row;

	public $comparator = '=';

	public $exclude;

	public $message = 'Record already exists';

}