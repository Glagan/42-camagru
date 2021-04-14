<?php namespace Controller;

use Camagru\Controller;
use Camagru\Http\JSONResponse;
use Camagru\Http\Response;
use Camagru\Mail;
use Env;
use Models\Comment;
use Models\Creation;
use Models\Like;
use Models\User;
use SQL\Query;

class Creations extends Controller
{
	const MODEL_COLUMNS = ['id', 'user', 'name', 'animated', 'at'];

	/**
	 * @param int $page The page number > 0
	 * @return \Camagru\Http\JSONResponse
	 */
	function list(int $page = 1): JSONResponse {
		if ($page < 1) {
			$page = 1;
		}

		$images = Creation::select()
			->columns(self::MODEL_COLUMNS)
			->where(['private' => false]) // ? OR image.user = auth.user
			->page($page, 10)
			->all(Creation::class);
		$result = [];
		foreach ($images as $image) {
			$result[] = $image->toArray(self::MODEL_COLUMNS);
		}

		return $this->json(['images' => $result]);
	}

	/**
	 * @param int $page The User ID
	 * @param int $page The page number > 0
	 * @return \Camagru\Http\JSONResponse
	 */
	public function user(int $id, int $page = 1): JSONResponse
	{
		if ($page < 1) {
			$page = 1;
		}
		if ($id < 1) {
			return $this->json(['error' => 'Invalid User ID.'], Response::BAD_REQUEST);
		}

		if ($this->auth->isLoggedIn() && $this->user->id == $id) {
			$user = $this->user;
			$private = true;
		} else {
			$user = User::get($id);
			if ($user === false) {
				return $this->json(['error' => 'User not found.'], Response::NOT_FOUND);
			}
			$private = false;
		}

		// Filter out private Images if it's not looking at our profile
		$conditions = ['user' => $id];
		if (!$private) {
			$conditions['private'] = false;
		}
		$images = Creation::select()
			->columns(self::MODEL_COLUMNS)
			->where($conditions)
			->page($page, 10)
			->all(Creation::class);
		$result = [];
		foreach ($images as $image) {
			$result[] = $image->toArray(self::MODEL_COLUMNS);
		}

		if ($page > 1) {
			return $this->json([
				'images' => $result,
			]);
		}
		return $this->json([
			'user' => $user->toArray(['id', 'username', 'verified']),
			'images' => $result,
		]);
	}

	/**
	 * @param int $id Image ID
	 * @return \Camagru\Http\JSONResponse
	 */
	public function like(int $id): JSONResponse
	{
		if ($id < 0) {
			return $this->json(['error' => 'Invalid Image ID.'], Response::BAD_REQUEST);
		}

		$image = Creation::get($id);
		if ($image === false) {
			return $this->json(['error' => 'Image not found.'], Response::NOT_FOUND);
		}
		if ($this->user->id == $image->user) {
			return $this->json(['error' => 'You can\'t like your own Image.'], Response::BAD_REQUEST);
		}
		if ($image->private && $this->user->id !== $image->user) {
			return $this->json(['error' => 'Private Image.'], Response::UNAUTHORIZED);
		}

		$like = Like::first(['user' => $this->user->id, 'image' => $id]);
		if ($like === false) {
			$like = new Like(['user' => $this->user->id, 'image' => $id]);
			$like->persist();
		} else {
			$like->remove();
		}

		$total = Like::count(['image' => $image->id]);
		$likePresent = $like->id !== null;
		$message = $likePresent ? 'Like added.' : 'Like removed.';
		return $this->json([
			'success' => $message,
			'total' => $total,
			'liked' => $likePresent,
		]);
	}

	/**
	 * @param int $id Image ID
	 * @return \Camagru\Http\JSONResponse
	 */
	public function comment(int $id): JSONResponse
	{
		$this->validate([
			'message' => [
				'min' => 1,
				'max' => 16384,
			],
		]);
		if ($id < 0) {
			return $this->json(['error' => 'Invalid Image ID.'], Response::BAD_REQUEST);
		}
		if (!$this->user->verified) {
			return $this->json(['error' => 'You need to be verified to post a comment.'], Response::UNAUTHORIZED);
		}

		$image = Creation::get($id);
		if ($image === false) {
			return $this->json(['error' => 'Image not found.'], Response::NOT_FOUND);
		}
		if ($image->private && $this->user->id !== $image->user) {
			return $this->json(['error' => 'Private Image.'], Response::UNAUTHORIZED);
		}

		$comment = new Comment([
			'image' => $id,
			'user' => $this->user->id,
			'message' => $this->input->get('message'),
		]);
		$comment->persist();

		// Send an email to the Image author if he have the option enabled
		if ($image->user !== $this->user->id) {
			$author = User::get($image->user);
			if ($author->receiveComments) {
				$link = Env::get('Camagru', 'url') . "/{$image->id}";
				Mail::send(
					$author,
					'[camagru] New comment',
					[
						"<b>{$this->user->username}</b> posted a new comment on one of your creations !",
						"You can see it there: <a href=\"{$link}\" rel=\"noreferer noopener\">{$link}</a>",
					]
				);
			}
		}

		return $this->json(['success' => 'Comment added.', 'id' => $comment->id]);
	}

	/**
	 * @param int $id Image ID
	 * @return \Camagru\Http\JSONResponse
	 */
	public function deleteSingle(int $id): JSONResponse
	{
		if ($id < 1) {
			return $this->json(['error' => 'Invalid Image ID.'], Response::BAD_REQUEST);
		}

		// Image
		$image = Creation::get($id);
		if ($image === false) {
			return $this->json(['error' => 'Image not found.'], Response::NOT_FOUND);
		}
		if ($this->user->id != $image->user) {
			return $this->json(['error' => 'Forbidden.'], Response::FORBIDDEN);
		}

		// Relations get automatically deleted
		unlink(Env::get('Camagru', 'uploads') . "/{$image->name}");
		$image->remove();

		return $this->json(['success' => 'Image deleted.']);
	}

	/**
	 * @param int $id Image ID
	 * @return \Camagru\Http\JSONResponse
	 */
	public function single(int $id): JSONResponse
	{
		if ($id < 1) {
			return $this->json(['error' => 'Invalid Image ID.'], Response::BAD_REQUEST);
		}

		// Image
		$image = Creation::get($id);
		if ($image === false) {
			return $this->json(['error' => 'Image not found.'], Response::NOT_FOUND);
		}
		if ($image->private && (!$this->auth->isLoggedIn() || $this->user->id != $image->user)) {
			return $this->json(['error' => 'Private Image.'], Response::UNAUTHORIZED);
		}

		// User
		if ($this->auth->isLoggedIn() && $this->user->id == $image->user) {
			$user = $this->user;
		} else {
			$user = User::get($image->user);
		}

		// Likes
		$likeCount = Like::count(['image' => $image->id]);
		$liked = false;
		if ($this->auth->isLoggedIn() && $this->user->id != $image->user) {
			$liked = Like::first(['image' => $image->id, 'user' => $this->user->id]) !== false;
		}

		// Comments
		$comments = Comment::all(
			['image' => $image->id, 'deleted' => false],
			['id' => Query::DESC]
		);
		// Find all Users associated to each comments
		$userIDs = \array_unique(
			\array_reduce($comments, function (array $carry, Comment $comment): array{
				$carry[] = $comment->user;
				return $carry;
			}, [])
		);
		$foundComments = [];
		if (\count($userIDs) > 0) {
			$users = User::all(['id' => $userIDs]);
			foreach ($comments as $comment) {
				$foundUser = null;
				foreach ($users as $commentUser) {
					if ($commentUser->id == $comment->user) {
						$foundUser = $commentUser;
						break;
					}
				}
				if ($foundUser !== null) {
					$foundComment = $comment->toArray(['id', 'user', 'message', 'at']);
					$foundComment['user'] = $foundUser->toArray(['id', 'username', 'verified']);
					$foundComments[] = $foundComment;
				}
			}
		}

		return $this->json([
			'image' => $image->toArray(self::MODEL_COLUMNS),
			'user' => $user->toArray(['id', 'username', 'verified']),
			'likes' => $likeCount,
			'liked' => $liked,
			'comments' => $foundComments,
		]);
	}
}
