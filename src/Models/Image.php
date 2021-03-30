<?php namespace Models;

use Camagru\Model;

/**
 * @property int $id
 * @property int $user
 * @property string $name
 * @property bool $private
 */
class Image extends Model
{
	/**
	 * @var array
	 */
	protected static $fields = [
		'user',
		'name',
		'private',
	];

	/**
	 * @var array
	 */
	protected static $casts = [
		'user' => User::class,
		'private' => 'bool',
	];
}
