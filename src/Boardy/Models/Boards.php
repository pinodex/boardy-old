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

class Boards extends Model {

	protected $table = 'boards';

	public $timestamps = false;

}