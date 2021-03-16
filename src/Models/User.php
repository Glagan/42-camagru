<?php namespace Models;

use Camagru\Model;

/**
 * @property int $id
 * @property string $username
 * @property string $mail
 * @property string $password
 * @property bool $verified
 * @property {'light'|'dark'} $theme
 * @property bool $receiveComments
 */
class User extends Model
{
	protected static $fields = [
		'username',
		'mail',
		'password',
		'verified',
		'theme',
		'receiveComments',
	];

	protected static $casts = [
		'verified' => 'bool',
		'theme' => ['light', 'dark'],
		'receiveComments' => 'bool',
	];
}
