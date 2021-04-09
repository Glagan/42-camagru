<?php namespace Models;

use Camagru\Model;

/**
 * @property int $id
 * @property int $user
 * @property string $name
 * @property bool $animated
 * @property bool $private
 * @property \DateTime $at
 */
class Creation extends Model
{
	/**
	 * @var array
	 */
	protected static $fields = [
		'user',
		'name',
		'animated',
		'private',
		'at',
	];

	/**
	 * @var array
	 */
	protected static $casts = [
		'user' => User::class,
		'animated' => 'bool',
		'private' => 'bool',
		'at' => 'date',
	];
}
