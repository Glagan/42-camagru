<?php namespace Controller;

use Camagru\Controller;
use Camagru\Http\JSONResponse;
use Models\Decoration as DecorationModel;

class Decoration extends Controller
{
	/**
	 * @return \Camagru\Http\JSONResponse
	 */
	function list(): JSONResponse {
		$all = DecorationModel::all(['public' => true]);
		$list = [];
		foreach ($all as $decoration) {
			$list[] = $decoration->toArray(['id', 'name', 'type']);
		}
		return $this->json(['list' => $list]);
	}

	/**
	 * @return \Camagru\Http\JSONResponse
	 */
	public function filtered(string $category): JSONResponse
	{
		$all = DecorationModel::all(['category' => $category, 'public' => true]);
		$list = [];
		foreach ($all as $decoration) {
			$list[] = $decoration->toArray(['id', 'name', 'type']);
		}
		return $this->json(['list' => $list]);
	}
}
