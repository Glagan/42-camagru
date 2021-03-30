<?php namespace Models;

use Camagru\Model;

/**
 * @property int $id
 * @property int $image
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
		'image',
		'user',
		'at',
		'message',
	];

	/**
	 * @var array
	 */
	protected static $casts = [
		'image' => Image::class,
		'user' => User::class,
		'at' => 'date',
	];
}
