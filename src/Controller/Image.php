<?php namespace Controller;

use Camagru\Controller;
use Camagru\Http\JSONResponse;
use Camagru\Http\Response;
use Camagru\Mail;
use Env;
use FFMPEG;
use Models\Comment;
use Models\Decoration;
use Models\Image as ImageModel;
use Models\Like;
use Models\User;
use SQL\Query;

class Image extends Controller
{
	const WIDTH = 1280;
	const HEIGHT = 720;
	const MODEL_COLUMNS = ['id', 'user', 'name', 'animated', 'at'];

	/**
	 * PNG ALPHA CHANNEL SUPPORT for imagecopymerge();
	 * by Sina Salek
	 * Bugfix by Ralph Voigt (bug which causes it
	 * to work only for $src_x = $src_y = 0.
	 * Also, inverting opacity is not necessary.)
	 * 08-JAN-2011
	 * @see https://www.php.net/manual/en/function.imagecopymerge.php#92787
	 **/
	private function imagecopymerge_alpha($dst_im, $src_im, int $dst_x, int $dst_y, int $src_x, int $src_y, int $src_w, int $src_h, int $pct): void
	{
		// creating a cut resource
		$cut = imagecreatetruecolor($src_w, $src_h);

		// copying relevant section from background to the cut resource
		imagecopy($cut, $dst_im, 0, 0, $dst_x, $dst_y, $src_w, $src_h);

		// copying relevant section from watermark to the cut resource
		imagecopy($cut, $src_im, 0, 0, $src_x, $src_y, $src_w, $src_h);

		// insert cut resource to destination image
		imagecopymerge($dst_im, $cut, $dst_x, $dst_y, 0, 0, $src_w, $src_h, $pct);
	}

	/**
	 * @return \Camagru\Http\JSONResponse
	 */
	public function upload(): JSONResponse
	{
		$this->validate([
			'upload' => [
				'type' => 'string',
			],
			'decorations' => [
				'type' => 'array',
			],
		]);

		if (!$this->user->verified) {
			return $this->json(['error' => 'You need to be verified to create an Image.'], Response::UNAUTHORIZED);
		}

		$upload = $this->input->get('upload');
		if ($upload == '') {
			return $this->json(['error' => 'Empty upload received'], Response::BAD_REQUEST);
		}
		if (3 * (\strlen($upload) / 4) > 5_000_000) {
			return $this->json(['error' => 'Upload size limit is 5 MB.'], Response::BAD_REQUEST);
		}

		// Check mime type in base64
		$match = [];
		if (!\preg_match('/(data:(image|video)\/(\w{2,5});base64,)/', $upload, $match)) {
			return $this->json(['error' => 'Could not find type in upload.'], Response::BAD_REQUEST);
		}
		$type = $match[2]; // image or video
		$extension = $match[3]; // webm, mp4, jpg, gif, png
		$isAnimated = $type == 'video' || $extension == 'gif';
		if ($isAnimated) {
			return $this->json(['error' => 'A source can\'t be animated.'], Response::BAD_REQUEST);
		}

		// Remove base64 mime type
		$upload = \mb_substr($upload, \mb_strlen($match[1]));
		$decodedUpload = \base64_decode($upload);
		if ($decodedUpload === false) {
			return $this->json(['error' => 'Empty or invalid upload.'], Response::BAD_REQUEST);
		}

		// Check decorations
		$rawDecorations = $this->input->get('decorations');
		$rawDecorations = \array_filter($rawDecorations, function ($decoration) {
			return \is_array($decoration)
			&& \array_key_exists('id', $decoration)
			&& \array_key_exists('position', $decoration)
			&& \is_array($decoration['position'])
			&& \array_key_exists('x', $decoration['position'])
			&& \array_key_exists('y', $decoration['position']);
		});
		if (\count($rawDecorations) < 1) {
			return $this->json(['error' => 'You need to add at least one Decoration.'], Response::BAD_REQUEST);
		}
		$decorations = Decoration::all([
			'id' => \array_map(function ($d) {
				return $d['id'];
			}, $rawDecorations),
			'public' => true,
		]);

		// Update raw decorations with the database for positions
		$foundDecorations = 0;
		foreach ($decorations as $decoration) {
			$isAnimated = $isAnimated || $decoration->animated;
			foreach ($rawDecorations as $key => $rawDecoration) {
				if ($rawDecoration['id'] == $decoration->id) {
					$rawDecorations[$key]['animated'] = $decoration->animated;
					$rawDecorations[$key]['name'] = $decoration->name;
					$rawDecorations[$key]['x'] = \round($rawDecoration['position']['x']);
					$rawDecorations[$key]['y'] = \round($rawDecoration['position']['y']);
					$foundDecorations++;
					break;
				}
			}
		}
		if ($foundDecorations < 1) {
			return $this->json(['error' => 'You need to add at least one valid Decoration.'], Response::BAD_REQUEST);
		}

		// Static background
		$resource = \imagecreatefromstring($decodedUpload);
		if ($resource === false) {
			return $this->json(['error' => 'Invalid or corrupted Image.'], Response::BAD_REQUEST);
		}
		if (\imagesx($resource) > self::WIDTH || \imagesy($resource) > self::HEIGHT) {
			return $this->json(['error' => "Maximum dimensions are " . self::WIDTH . "x" . self::HEIGHT . "px."], Response::BAD_REQUEST);
		}
		$now = (new \DateTime())->format('His');

		// Animated upload
		if ($isAnimated) {
			// Save backgrond image for FFMPEG
			$name = "{$now}_" . \bin2hex(\random_bytes(5)) . ".png";
			$tmpPath = Env::get('Camagru', 'tmp') . "/{$name}";
			$saved = \imagepng($resource, $tmpPath);
			\imagedestroy($resource);
			if ($saved === false) {
				return $this->json(['error' => 'Failed to save background Image.'], Response::BAD_REQUEST);
			}

			// Add all decorations on the source
			$ffmpeg = new FFMPEG();
			$output = "{$now}_" . \bin2hex(\random_bytes(5)) . ".webm";
			$result = $ffmpeg->decorate($tmpPath, $rawDecorations, Env::get('Camagru', 'uploads') . "/{$output}");

			// Clear
			\unlink($tmpPath);
			if ($result === false) {
				return $this->json(['error' => 'Failed to generate animated Image.'], Response::BAD_REQUEST);
			}
		}
		// Static upload
		else {
			\imagealphablending($resource, false);
			\imagesavealpha($resource, true);

			// Add decorations
			foreach ($rawDecorations as $rawDecoration) {
				$path = Env::get('Camagru', 'decorations') . "/{$rawDecoration['name']}";
				$decorationResource = \imagecreatefromstring(\file_get_contents($path));
				$position = $rawDecoration['position'];
				$size = [\imagesx($decorationResource), \imagesy($decorationResource)];
				$this->imagecopymerge_alpha(
					$resource,
					$decorationResource,
					$position['x'], $position['y'],
					0, 0,
					$size[0], $size[1],
					100
				);
			}

			// Save
			$output = "{$now}_" . \bin2hex(\random_bytes(5)) . ".png";
			$saved = \imagepng($resource, Env::get('Camagru', 'uploads') . "/{$output}");
			\imagedestroy($resource);
			if ($saved === false) {
				return $this->json(['error' => 'Failed to save background Image.'], Response::BAD_REQUEST);
			}
		}

		// Create Image model
		$creation = new ImageModel([
			'user' => $this->user->id,
			'name' => $output,
			'animated' => $isAnimated,
			'private' => false,
		]);
		$creation->persist();

		return $this->json(['success' => 'Creation uploaded.', 'id' => $creation->id]);
	}

	/**
	 * @param int $page The page number > 0
	 * @return \Camagru\Http\JSONResponse
	 */
	function list(int $page = 1): JSONResponse {
		if ($page < 1) {
			$page = 1;
		}

		$images = ImageModel::select()
			->columns(self::MODEL_COLUMNS)
			->where(['private' => false]) // ? OR image.user = auth.user
			->page($page, 10)
			->all(ImageModel::class);
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
		$images = ImageModel::select()
			->columns(self::MODEL_COLUMNS)
			->where($conditions)
			->page($page, 10)
			->all(ImageModel::class);
		$result = [];
		foreach ($images as $image) {
			$result[] = $image->toArray(self::MODEL_COLUMNS);
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

		$image = ImageModel::get($id);
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

		$image = ImageModel::get($id);
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

		return $this->json(['success' => 'Comment added.', 'id' => $comment->id]);
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
		$image = ImageModel::get($id);
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
