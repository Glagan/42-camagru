<?php namespace Models;

use Camagru\Model;

/**
 * Valid User sessions.
 * @property int $id
 * @property int $user
 * @property string $session
 */
class UserSession extends Model
{
	/**
	 * @var array
	 */
	protected static $fields = [
		'user',
		'session',
	];

	/**
	 * @var array
	 */
	protected static $casts = [
		'user' => User::class,
	];
}
