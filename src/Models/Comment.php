<?php namespace Models;

use Camagru\Model;

/**
 * @property int $id
 * @property int $creation
 * @property int $user
 * @property \DateTime $at
 * @property string $message
 */
class Comment extends Model
{
	/**
	 * @var array
	 */
	protected static $fields = [
		'creation',
		'user',
		'at',
		'message',
	];

	/**
	 * @var array
	 */
	protected static $casts = [
		'creation' => Creation::class,
		'user' => User::class,
		'at' => 'date',
	];
}
