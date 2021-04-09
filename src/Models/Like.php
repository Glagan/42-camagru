<?php namespace Models;

use Camagru\Model;

/**
 * @property int $id
 * @property int $creation
 * @property \DateTime $at
 */
class Like extends Model
{
	/**
	 * @var array
	 */
	protected static $fields = [
		'creation',
		'at',
	];

	/**
	 * @var array
	 */
	protected static $casts = [
		'creation' => Creation::class,
		'at' => 'date',
	];
}
