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

		// Check decorations
		$rawDecorations = $this->input->get('decorations');
		if (\count($rawDecorations) < 1) {
			return $this->json(['error' => 'You need to add at least 1 Decoration.'], Response::BAD_REQUEST);
		}
		if (\count($rawDecorations) > 5) {
			return $this->json(['error' => 'You can only add up to 5 Decorations.'], Response::BAD_REQUEST);
		}
		foreach ($rawDecorations as $decoration) {
			$validDecoration = (
				\array_key_exists('id', $decoration)
				&& \array_key_exists('position', $decoration)
				&& \is_array($decoration['position'])
				&& \array_key_exists('x', $decoration['position'])
				&& \array_key_exists('y', $decoration['position']));
			if (!$validDecoration) {
				return $this->json(['error' => "Invalid decoration #{$decoration['id']}, x: {$decoration['position']['x']} y: {$decoration['position']['y']}."], Response::BAD_REQUEST);
			}
		}
		$decorations = Decoration::all([
			'id' => \array_column($rawDecorations, 'id'),
			'public' => true,
		]);

		// Check if there is missing decorations and set the animated flag
		$animatedCount = 0;
		foreach ($rawDecorations as $key => $rawDecoration) {
			$foundDecoration = false;
			foreach ($decorations as $decoration) {
				if ($decoration->id == $rawDecoration['id']) {
					$rawDecorations[$key]['animated'] = $decoration->animated;
					if ($decoration->animated) {
						$animatedCount++;
					}
					$rawDecorations[$key]['name'] = $decoration->name;
					$foundDecoration = true;
					break;
				}
			}
			if (!$foundDecoration) {
				return $this->json(['error' => 'Invalid Decorations.'], Response::BAD_REQUEST);
			}
		}
		if ($animatedCount > 1) {
			return $this->json(['error' => 'You can only have 1 animated Decoration.'], Response::BAD_REQUEST);
		}

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

		// Set the required informations in rawDecorations
		$width = $image->width();
		$height = $image->height();
		foreach ($rawDecorations as $key => $rawDecoration) {
			// Positions are percentages on the X and Y axis
			$rawDecorations[$key]['x'] = \round($width * $rawDecoration['position']['x']);
			$rawDecorations[$key]['y'] = \round($height * $rawDecoration['position']['y']);
		}

		// If there is one animated image, we create everything in one command with FFMPEG
		// All images are added as layers on top of the background
		if ($animatedCount > 0) {
			// Save backgrond image for FFMPEG
			$name = "{$now}_" . \bin2hex(\random_bytes(5)) . ".png";
			$path = Env::get('Camagru', 'tmp') . "/{$name}";
			$saved = $image->save($path);
			if ($saved === false) {
				return $this->json(['error' => 'Failed to save background Image.'], Response::BAD_REQUEST);
			}

			// Add all decorations to the source
			$ffmpeg = new FFMPEG();
			$output = "{$now}_" . \bin2hex(\random_bytes(5)) . ".webm";
			$result = $ffmpeg->decorate($path, $rawDecorations, Env::get('Camagru', 'uploads') . "/{$output}");

			// Clear
			\unlink($path);
			if ($result === false) {
				return $this->json(['error' => 'Failed to generate animated Image.'], Response::INTERNAL_SERVER_ERROR);
			}
		}
		// If there is no animated images, they are all added incrementally on top of each others
		else {
			$width = $image->width();
			$height = $image->height();

			// Merge each decorations
			foreach ($rawDecorations as $rawDecoration) {
				$path = Env::get('Camagru', 'decorations') . "/{$rawDecoration['name']}";
				$decorationResource = Image::fromString(\file_get_contents($path));
				$image->merge($decorationResource, $rawDecoration['x'], $rawDecoration['y']);
			}

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
			'animated' => $animatedCount > 0,
		]);
		$creation->persist();

		return $this->json(['success' => 'Creation uploaded.', 'id' => $creation->id]);
	}

}
