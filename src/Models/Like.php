<?php namespace Models;

use Camagru\Model;

/**
 * @property int $id
 * @property Image $image
 * @property \DateTime $at
 */
class Like extends Model
{
	protected static $fields = [
		'image',
		'at',
	];

	protected static $casts = [
		'image' => Image::class,
		'at' => 'date',
	];
}
