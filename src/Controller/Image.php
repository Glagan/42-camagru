<?php namespace Controller;

use Camagru\Controller;
use Camagru\Http\Response;
use Models\Comment;
use Models\Image as ImageModel;
use Models\Like;
use Models\User;

class Image extends Controller
{
	/**
	 * @return \Camagru\Http\Response
	 */
	public function upload(): Response
	{
		// TODO
		return $this->json(['success' => 'Creation uploaded.']);
	}

	/**
	 * @param int $page The page number > 0
	 * @return \Camagru\Http\Response
	 */
	function list(int $page = 1): Response {
		if ($page < 1) {
			$page = 1;
		}
		$images = ImageModel::select()
			->columns(['id', 'user', 'name', 'at'])
			->where(['private' => false])
			->page($page, 10)
			->all(ImageModel::class);
		$result = [];
		foreach ($images as $image) {
			$result[] = $image->toArray(['id', 'user', 'name', 'at']);
		}
		// ? TODO: Linked User + Like count + Comments count
		return $this->json(['images' => $result]);
	}

	/**
	 * @param int $id Image ID
	 * @return \Camagru\Http\Response
	 */
	public function like(int $id): Response
	{
		if ($id < 0) {
			return $this->json(['error' => 'Invalid Image ID.'], Response::BAD_REQUEST);
		}

		$image = ImageModel::get($id);
		if ($image === false) {
			return $this->json(['error' => 'Image not found.'], Response::NOT_FOUND);
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

		$message = $like->id == null ? 'Like removed.' : 'Like added.';
		return $this->json(['success' => $message]);
	}

	/**
	 * @param int $id Image ID
	 * @return \Camagru\Http\Response
	 */
	public function comment(int $id): Response
	{
		$this->validate([
			'comment' => [
				'min' => 1,
				'max' => 16384,
			],
		]);
		if ($id < 0) {
			return $this->json(['error' => 'Invalid Image ID.'], Response::BAD_REQUEST);
		}

		$image = ImageModel::get($id);
		if ($image === false) {
			return $this->json(['error' => 'Image not found.'], Response::NOT_FOUND);
		}
		if ($image->private && $this->user->id !== $image->user) {
			return $this->json(['error' => 'Private Image.'], Response::UNAUTHORIZED);
		}

		$comment = new Comment([
			'user' => $this->user->id,
			'message' => $this->input->get('message'),
		]);
		$comment->persist();

		return $this->json(['success' => 'Comment added.']);
	}

	/**
	 * @param int $id Image ID
	 * @return \Camagru\Http\Response
	 */
	public function single(int $id): Response
	{
		if ($id < 1) {
			return $this->json(['error' => 'Invalid Image ID.'], Response::BAD_REQUEST);
		}

		// Image
		$image = ImageModel::get($id);
		if ($image === false) {
			return $this->json(['error' => 'Image not found.'], Response::NOT_FOUND);
		}
		if ($image->private && (!$this->auth->isLoggedIn() || $this->user->id != $image->user)) {
			return $this->json(['error' => 'Private Image.'], Response::UNAUTHORIZED);
		}

		// User
		if ($this->auth->isLoggedIn() && $this->user == $image->user) {
			$user = $this->user;
		} else {
			$user = User::first(['id' => $image->user]);
		}

		// Likes
		$likeCount = Like::count(['image' => $image->id]);

		// Comments
		// TODO: comment->user->username
		$comments = Comment::all(['image' => $image->id, 'deleted' => false]);
		$foundComments = [];
		foreach ($comments as $comment) {
			$foundComments[] = $comment->toArray(['id', 'user', 'message', 'at']);
		}

		return $this->json([
			'image' => $image->toArray(['id', 'user', 'name', 'at']),
			'user' => $user->toArray(['id', 'username', 'verified']),
			'likes' => $likeCount,
			'comments' => $foundComments,
		]);
	}
}
