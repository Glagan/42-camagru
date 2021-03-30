<?php namespace Models;

use Camagru\Model;

/**
 * @property int $id
 * @property int $image
 * @property \DateTime $at
 */
class Like extends Model
{
	/**
	 * @var array
	 */
	protected static $fields = [
		'image',
		'at',
	];

	/**
	 * @var array
	 */
	protected static $casts = [
		'image' => Image::class,
		'at' => 'date',
	];
}
