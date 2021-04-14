<?php namespace Controller;

use Camagru\Controller;
use Camagru\Http\JSONResponse;
use Camagru\Http\Response;
use Env;
use FFMPEG;
use Image;
use Models\Creation;
use Models\Decoration;

class Create extends Controller
{
	const MIN_WIDTH = 854;
	const MAX_WIDTH = 2560;
	const MIN_HEIGHT = 480;
	const MAX_HEIGHT = 1440;

	/**
	 * @return \Camagru\Http\JSONResponse
	 */
	public function upload(): JSONResponse
	{
		$this->validate([
			'upload' => [
				'type' => 'string',
			],
			'decoration' => [
				'type' => 'array',
			],
			'scale' => [
				'type' => 'int',
			],
		]);

		if (!$this->user->verified) {
			return $this->json(['error' => 'You need to be verified to create an Image.'], Response::UNAUTHORIZED);
		}

		$upload = $this->input->get('upload');
		if ($upload == '') {
			return $this->json(['error' => 'Empty upload received'], Response::BAD_REQUEST);
		}
		if (3 * (\strlen($upload) / 4) > 10_000_000) {
			return $this->json(['error' => 'Upload size limit is 10 MB.'], Response::BAD_REQUEST);
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

		// Check decoration
		$rawDecoration = $this->input->get('decoration');
		$validDecoration = (
			\array_key_exists('id', $rawDecoration)
			&& \array_key_exists('position', $rawDecoration)
			&& \is_array($rawDecoration['position'])
			&& \array_key_exists('x', $rawDecoration['position'])
			&& \array_key_exists('y', $rawDecoration['position']));
		if (!$validDecoration) {
			return $this->json(['error' => 'You need to add one valid Decoration.'], Response::BAD_REQUEST);
		}
		$decoration = Decoration::first([
			'id' => $rawDecoration['id'],
			'public' => true,
		]);
		if ($decoration === false) {
			return $this->json(['error' => 'You need to add one valid Decoration.'], Response::BAD_REQUEST);
		}

		// Update raw decoration with the database for animated state and path
		$isAnimated = $decoration->animated;
		$rawDecoration['animated'] = $decoration->animated;
		$rawDecoration['name'] = $decoration->name;
		$scale = $this->input->get('scale');
		if ($scale <= 0) {
			return $this->json(['error' => 'The Image scale must be more than 0.'], Response::BAD_REQUEST);
		}
		$rawDecoration['x'] = \round($rawDecoration['position']['x'] / $scale);
		$rawDecoration['y'] = \round($rawDecoration['position']['y'] / $scale);

		// Static background
		$image = Image::fromString($decodedUpload);
		if ($image === false) {
			return $this->json(['error' => 'Invalid or corrupted Image.'], Response::BAD_REQUEST);
		}
		if ($image->width() < self::MIN_WIDTH || $image->height() < self::MIN_HEIGHT) {
			return $this->json(['error' => "Minimum dimensions are " . self::MIN_WIDTH . "x" . self::MIN_HEIGHT . "px."], Response::BAD_REQUEST);
		}
		if ($image->width() > self::MAX_WIDTH || $image->height() > self::MAX_HEIGHT) {
			return $this->json(['error' => "Maximum dimensions are " . self::MAX_WIDTH . "x" . self::MAX_HEIGHT . "px."], Response::BAD_REQUEST);
		}
		$now = (new \DateTime())->format('His');

		// Animated upload
		if ($isAnimated) {
			// Save backgrond image for FFMPEG
			$name = "{$now}_" . \bin2hex(\random_bytes(5)) . ".png";
			$path = Env::get('Camagru', 'tmp') . "/{$name}";
			$saved = $image->save($path);
			if ($saved === false) {
				return $this->json(['error' => 'Failed to save background Image.'], Response::BAD_REQUEST);
			}

			// Add all decorations on the source
			$ffmpeg = new FFMPEG();
			$output = "{$now}_" . \bin2hex(\random_bytes(5)) . ".webm";
			$result = $ffmpeg->decorate($path, $rawDecoration, Env::get('Camagru', 'uploads') . "/{$output}");

			// Clear
			\unlink($path);
			if ($result === false) {
				return $this->json(['error' => 'Failed to generate animated Image.'], Response::INTERNAL_SERVER_ERROR);
			}
		}
		// Static upload
		else {
			// Load decoration
			$path = Env::get('Camagru', 'decorations') . "/{$rawDecoration['name']}";
			$decorationResource = Image::fromString(\file_get_contents($path));
			$image->merge($decorationResource, $rawDecoration['x'], $rawDecoration['y']);
			// Save
			$output = "{$now}_" . \bin2hex(\random_bytes(5)) . ".png";
			$saved = $image->save(Env::get('Camagru', 'uploads') . "/{$output}");
			if ($saved === false) {
				return $this->json(['error' => 'Failed to save background Image.'], Response::BAD_REQUEST);
			}
		}

		// Create Image model
		$creation = new Creation([
			'user' => $this->user->id,
			'name' => $output,
			'animated' => $isAnimated,
			'private' => false,
		]);
		$creation->persist();

		return $this->json(['success' => 'Creation uploaded.', 'id' => $creation->id]);
	}

}
