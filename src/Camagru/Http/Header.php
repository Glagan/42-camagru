<?php namespace Camagru\Http;

abstract class Header
{
	const ACCEPT_TYPE = 'Accept';
	const CONTENT_TYPE = 'Content-Type';
	const ACCEPT_ENCODING = 'Accept-Encoding';
	const CONTENT_ENCODING = 'Content-Encoding';
	const CONTENT_LENGTH = 'Content-Length';

	const JSON_TYPE = 'application/json';
	const JSON_TYPE_UTF8 = 'application/json; charset=utf-8';
}
