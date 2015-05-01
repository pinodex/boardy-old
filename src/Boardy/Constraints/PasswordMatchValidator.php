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
use Boardy\Utils\Hash;

class PasswordMatchValidator extends ConstraintValidator {

	public function validate($value, Constraint $constraint) {
		if (!Hash::check($value, $constraint->to)) {
			$this->context->addViolation($constraint->message);
		}
	}

}