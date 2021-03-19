<?php namespace Models;

use Camagru\Model;

/**
 * @property int $id
 * @property string $username
 * @property string $email
 * @property string $password
 * @property bool $verified
 * @property {'light'|'dark'} $theme
 * @property bool $receiveComments
 */
class User extends Model
{
	protected static $fields = [
		'username',
		'email',
		'password',
		'verified',
		'theme',
		'receiveComments',
	];

	protected static $casts = [
		'verified' => 'bool',
		'receiveComments' => 'bool',
	];

	protected static $defaults = [
		'theme' => 'light',
	];
}
