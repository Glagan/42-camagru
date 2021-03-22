<?php namespace Models;

use Camagru\Model;

/**
 * @property int $id
 * @property User $user
 * @property string $path
 * @property bool $private
 */
class Image extends Model
{
	/**
	 * @var array
	 */
	protected static $fields = [
		'user',
		'path',
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
