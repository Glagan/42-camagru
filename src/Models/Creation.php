<?php namespace Models;

use Camagru\Model;

/**
 * @property int $id
 * @property int $user
 * @property string $name
 * @property bool $animated
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
		'at',
	];

	/**
	 * @var array
	 */
	protected static $casts = [
		'user' => User::class,
		'animated' => 'bool',
		'at' => 'date',
	];
}
