<?php

class FFMPEG
{
	/**
	 * ffmpeg
	 * 	filter force use rgba (yuva420p on webm) on both inputs and overlay on given position
	 * 	output with yuva420p for alpha channel with shortest inputs (webm).
	 */
	public function decorate(string $source, array $decoration, int $scale, string $output): bool
	{
		$filter = ["[0]format=rgba[background];"];
		$filter[] = "[1:v]format=rgba[decoration];";
		$filter[] = "[background][decoration]overlay={$decoration['x']}:{$decoration['y']}";
		$filter = \implode('', $filter);
		$command = ["ffmpeg"];
		$command[] = "-c:v png -i \"{$source}\""; // Background
		$path = \realpath(Env::get('Camagru', 'decorations') . "/{$decoration['name']}");
		$command[] = "-vcodec libvpx-vp9 -i \"{$path}\""; // Decoration
		$command[] = "-filter_complex \"{$filter}\" -shortest -crf 18 -c:v libvpx-vp9 -pix_fmt yuva420p -movflags +faststart -y {$output}";
		$command = \implode(' ', $command);
		Log::debug('FFMPEG command', $command);
		$output = [];
		$code = 0;
		$result = \exec($command, $output, $code);
		//Log::debug('FFMPEG result: ' . $code, $output);
		return $result !== false && $code === 0;
	}
}
