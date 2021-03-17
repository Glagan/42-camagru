<?php namespace Models;

use Camagru\Model;

/**
 * Valid User sessions.
 * @property int $id
 * @property User $user
 * @property string $session
 */
class UserSession extends Model
{
	protected static $fields = [
		'user',
		'session',
	];

	protected static $casts = [
		'user' => User::class,
	];
}
