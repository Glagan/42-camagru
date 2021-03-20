<?php namespace Models;

use Camagru\Model;

/**
 * @property int $id
 * @property Image $image
 * @property User $user
 * @property \DateTime $at
 * @property string $message
 */
class Comment extends Model
{
	protected static $fields = [
		'image',
		'user',
		'at',
		'message',
	];

	protected static $casts = [
		'image' => Image::class,
		'user' => User::class,
		'at' => 'date',
	];
}
