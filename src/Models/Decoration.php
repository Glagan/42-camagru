<?php namespace Models;

use Camagru\Model;

/**
 * A Decoration on top of uploaded images or videos.
 * @property int $id
 * @property string $name
 * @property string $category
 * @property bool $public
 */
class Decoration extends Model
{
	/**
	 * @var array
	 */
	protected static $fields = [
		'name',
		'category',
		'public',
	];

	/**
	 * @var array
	 */
	protected static $casts = [
		'public' => 'bool',
	];
}
