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

class UsersSession extends Model {

	protected $table = 'users_sessions';

	protected $primaryKey = 'hash';

	public $timestamps = false;

}