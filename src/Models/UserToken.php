<?php namespace Models;

use Camagru\Model;

/**
 * Valid User sessions.
 * @property int $id
 * @property int $user
 * @property string $token
 * @property string $scope
 * @property \DateTime $issued
 */
class UserToken extends Model
{
	/**
	 * @var array
	 */
	protected static $fields = [
		'user',
		'token',
		'scope',
		'issued',
	];

	/**
	 * @var array
	 */
	protected static $casts = [
		'user' => User::class,
		'issued' => 'date',
	];

	/**
	 * Create a new UserToken with a random 50 characters long string.
	 * @param integer $user
	 * @param string $scope
	 * @return UserToken
	 */
	public static function generate(int $user, string $scope): UserToken
	{
		$token = new UserToken(['user' => $user, 'scope' => $scope]);
		$token->token = \bin2hex(\random_bytes(25));
		return $token;
	}
}
