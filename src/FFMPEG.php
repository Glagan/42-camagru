<?php

class FFMPEG
{
	/**
	 * ffmpeg
	 * 	filter force use rgba (yuva420p on webm) on both inputs and overlay on given position
	 * 	output with yuva420p for alpha channel with shortest inputs (webm).
	 */
	public function decorate(string $source, array $decorations, string $output): bool
	{
		// Construct filter
		$filter = ["[0]format=rgba[background];"];
		$index = 1;
		foreach ($decorations as $decoration) {
			$filter[] = "[{$index}]format=rgba[decoration{$index}];";
			$index++;
		}
		// Merge
		$filter = [];
		$index = 1;
		$last = \count($decorations);
		$previous = '0';
		foreach ($decorations as $decoration) {
			$filter[] = "[{$previous}][{$index}]overlay={$decoration['x']}:{$decoration['y']}[o{$index}]";
			if ($index < $last) {
				$filter[] = ";";
			}
			$previous = "o{$index}";
			$index++;
		}
		$filter = \implode('', $filter);

		// Construct command
		$command = ["ffmpeg"];
		$command[] = "-c:v png -i \"{$source}\""; // Background
		foreach ($decorations as $decoration) {
			$path = \realpath(Env::get('Camagru', 'decorations') . "/{$decoration['name']}");
			if ($decoration['animated']) {
				$command[] = "-vcodec libvpx-vp9 -i \"{$path}\"";
			} else {
				$command[] = "-i \"{$path}\"";
			}
		}
		$command[] = "-filter_complex \"{$filter}\" -map \"[o{$last}]\" -shortest -crf 18 -c:v libvpx-vp9 -pix_fmt yuva420p -movflags +faststart -y {$output}";
		$command = \implode(' ', $command);

		// Start
		Log::debug('FFMPEG command', $command);
		$output = [];
		$code = 0;
		$result = \exec($command, $output, $code);
		//Log::debug('FFMPEG result: ' . $code, $output);
		return $result !== false && $code === 0;
	}
}
