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
use Symfony\Component\Validator\ConstraintValidator;

class RecordExistenceValidator extends ConstraintValidator {

	public function validate($value, Constraint $constraint) {
		if ($constraint->exclude && $constraint->exclude == $value) {
			return true;
		}

		$valid = $constraint->model->where($constraint->row, $constraint->comparator, $value)->exists();

		if ($constraint->validate == 'exists' && $valid) {
			$this->context->addViolation($constraint->message);
		}

		if ($constraint->validate == 'not_exists' && !$valid) {
			$this->context->addViolation($constraint->message);
		}
	}

}