<?php namespace Models;

use Camagru\Model;

/**
 * @property int $id
 * @property string $username
 * @property string $email
 * @property string $password
 * @property bool $verified
 * @property bool $receiveComments
 */
class User extends Model
{
	/**
	 * @var array
	 */
	protected static $fields = [
		'username',
		'email',
		'password',
		'verified',
		'receiveComments',
	];

	/**
	 * @var array
	 */
	protected static $casts = [
		'verified' => 'bool',
		'receiveComments' => 'bool',
	];
}
