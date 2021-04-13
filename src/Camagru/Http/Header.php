<?php namespace Camagru\Http;

abstract class Header
{
	const ACCEPT_TYPE = 'Accept';
	const CONTENT_TYPE = 'Content-Type';
	const ACCEPT_ENCODING = 'Accept-Encoding';
	const CONTENT_ENCODING = 'Content-Encoding';
	const CONTENT_LENGTH = 'Content-Length';
	const CSP = 'Content-Security-Policy';
	const X_FRAMES_OPTIONS = 'X-Frame-Options';
	const X_XSS_PROTECTION = 'X-XSS-Protection';
	const X_CONTENTTYPE_OPTIONS = 'X-Content-Type-Options';
	const STRICT_TRANSPORT_SECURITY = 'Strict-Transport-Security';

	const JSON_TYPE = 'application/json';
	const JSON_TYPE_UTF8 = 'application/json; charset=utf-8';
}
