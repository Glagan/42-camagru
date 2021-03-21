<?php namespace Controller;

use Camagru\Controller;
use Camagru\Http\Response;
use Models\Comment;
use Models\Image as ImageModel;
use Models\Like;

class Image extends Controller
{
	public function upload()
	{
		// TODO
		return $this->json(['success' => 'Creation uploaded.']);
	}

	function list($page = 1) {
		if ($page < 1) {
			$page = 1;
		}
		$images = ImageModel::select()
			->columns(['id', 'user', 'path', 'at'])
			->where(['private' => false])
			->page($page, 10)
			->all(ImageModel::class);
		// TODO: attributes of Models in an array are not found
		// TODO: Linked User + Like count + Comments count
		return $this->json(['images' => $images]);
	}

	public function like($id)
	{
		if ($id < 0) {
			return $this->json(['error' => 'Invalid Image ID.'], Response::BAD_REQUEST);
		}
		$image = ImageModel::get($id);
		if ($image === false) {
			return $this->json(['error' => 'Image not found.'], Response::NOT_FOUND);
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

	public function comment($id)
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
		$comment = new Comment([
			'user' => $this->user->id,
			'message' => $this->input->get('message'),
		]);
		$comment->persist();
		return $this->json(['success' => 'Comment added.']);
	}

	public function single($id)
	{
		if ($id < 1) {
			return $this->json(['error' => 'Invalid Image ID.'], Response::BAD_REQUEST);
		}
		$image = ImageModel::get($id);
		if ($image === false) {
			return $this->json(['error' => 'Image not found.'], Response::NOT_FOUND);
		}
		$attributes = $image->toArray(['id', 'user', 'path', 'at']);
		// TODO: Linked user + Likes + Comments
		return $this->json(['image' => $attributes]);
	}
}
