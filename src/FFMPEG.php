<?php

class FFMPEG
{
	/**
	 * ffmpeg
	 * [{-vcodec libvpx-vp9} -i decoration.webm]
	 *		required with animated decorations to keep transparency
	 * -i source.png
	 *		Add the source last to avoid problems when assigning vcodec manually
	 * -filter_complex "[1:v]{[0:v]overlay=25:25}"
	 *		Filter to add each decorations on top of the background
	 * -shortest -c:a copy -y output.webm
	 *		Loop with the shortest animated decoration, copy codecs and erase output if it already exists
	 */
	public function decorate(string $source, array $decorations, string $output): bool
	{
		$command = ['ffmpeg'];
		$totalDecorations = \count($decorations);
		$filter = ["[{$totalDecorations}:v]"];
		foreach ($decorations as $decoration) {
			if ($decoration['animated']) {
				$command[] = "-vcodec libvpx-vp9";
			}
			$command[] = "-i \"/var/www/html/storage/decorations/{$decoration['name']}\"";
			$totalDecorations--;
			$filter[] = "[{$totalDecorations}:v]overlay={$decoration['x']}:{$decoration['y']}";
		}
		$filterCommand = \implode('', $filter);
		$command[] = "-i \"{$source}\" -filter_complex \"{$filterCommand}\"";
		$command[] = "-c:a copy -shortest -y {$output}";
		$command = \implode(' ', $command);
		Log::debug('FFMPEG command', $command);
		$output = [];
		$code = 0;
		$result = \exec($command, $output, $code);
		Log::debug('FFMPEG result: ' . $code, $output);
		return $result !== false && $code === 0;
	}
}
